import { useAuthStore } from '@/stores/auth';

interface RequestOptions extends RequestInit {
  headers?: Record<string, string>;
}

export const useApi = () => {
  const authStore = useAuthStore();

  const request = async (
    url: string,
    options: RequestOptions = {}
  ): Promise<Response> => {
    const headers: Record<string, string> = {
      'Accept': 'application/json',
      ...options.headers,
    };

    // Only set Content-Type for non-FormData requests with a body
    if (options.body && !(options.body instanceof FormData)) {
      headers['Content-Type'] = 'application/json';
    }

    // Add authorization header if token exists
    if (authStore.token) {
      headers['Authorization'] = `Bearer ${authStore.token}`;
    }

    const response = await fetch(url, {
      ...options,
      headers,
    });

    // Handle rate limiting (429) centrally with a friendly message + metadata (include Retry-After when available)
    if (response.status === 429) {
      // Try to extract structured body if present
      let message = 'Too many requests — please try again in a moment.';
      let body: any = null;
      try {
        body = await response.clone().json().catch(() => null);
        if (body && body.message) {
          message = body.message;
        }
      } catch (e) {
        // ignore parsing errors
      }

      const retryHeader = response.headers.get('Retry-After') || response.headers.get('retry-after');
      // Resolve retryAfter in seconds where possible (prefer body.retry_after)
      let retryAfterSeconds: number | null = null;

      if (body && (body.retry_after || body.retryAfter)) {
        const ra = body.retry_after ?? body.retryAfter;
        const n = Number(ra);
        if (!isNaN(n)) retryAfterSeconds = Math.max(0, Math.floor(n));
      } else if (retryHeader) {
        const n = parseInt(retryHeader, 10);
        if (!isNaN(n)) {
          retryAfterSeconds = Math.max(0, n);
        } else {
          const dateMs = Date.parse(retryHeader);
          if (!isNaN(dateMs)) {
            retryAfterSeconds = Math.max(0, Math.ceil((dateMs - Date.now()) / 1000));
          }
        }
      }

      // Prefer message that contains retry info when available
      if (retryAfterSeconds !== null) {
        message = `Too many requests — please retry after ${retryAfterSeconds} second${retryAfterSeconds === 1 ? '' : 's'}.`;
      } else if (body && body.retry_after) {
        message = body.message || `Too many requests — retry after ${body.retry_after}.`;
      }

      const err: any = new Error(message);
      err.status = 429;
      err.code = 'RATE_LIMIT';
      err.retryAfter = retryAfterSeconds; // number of seconds or null
      err.retryAfterRaw = retryHeader ?? (body?.retry_after ?? null);
      throw err;
    }

    // If unauthorized, clear auth
    if (response.status === 401) {
      await authStore.logout();
    }

    return response;
  };

  const get = async (url: string, options: RequestOptions = {}): Promise<Response> => {
    return request(url, { ...options, method: 'GET' });
  };

  const post = async (url: string, data?: any, options: RequestOptions = {}): Promise<Response> => {
    const isFormData = data instanceof FormData;
    
    return request(url, {
      ...options,
      method: 'POST',
      body: data ? (isFormData ? data : JSON.stringify(data)) : null,
    } as RequestOptions);
  };

  const put = async (url: string, data?: any, options: RequestOptions = {}): Promise<Response> => {
    const isFormData = data instanceof FormData;
    
    // For FormData with PUT, we need to use POST with _method=PUT
    if (isFormData) {
      data.append('_method', 'PUT');
      return request(url, {
        ...options,
        method: 'POST',
        body: data,
      } as RequestOptions);
    }
    
    return request(url, {
      ...options,
      method: 'PUT',
      body: data ? JSON.stringify(data) : null,
    } as RequestOptions);
  };

  const patch = async (url: string, data?: any, options: RequestOptions = {}): Promise<Response> => {
    const isFormData = data instanceof FormData;
    
    // For FormData with PATCH, we need to use POST with _method=PATCH
    if (isFormData) {
      data.append('_method', 'PATCH');
      return request(url, {
        ...options,
        method: 'POST',
        body: data,
      } as RequestOptions);
    }
    
    return request(url, {
      ...options,
      method: 'PATCH',
      body: data ? JSON.stringify(data) : null,
    } as RequestOptions);
  };

  const del = async (url: string, options: RequestOptions = {}): Promise<Response> => {
    return request(url, { ...options, method: 'DELETE' });
  };

  return {
    request,
    get,
    post,
    put,
    patch,
    del,
  };
};
