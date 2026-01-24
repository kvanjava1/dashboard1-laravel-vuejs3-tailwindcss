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
