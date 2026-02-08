export interface DebouncedFunction<T extends (...args: any[]) => any> {
    (...args: Parameters<T>): void;
    cancel(): void;
    flush(...args: Parameters<T>): void;
}

export function debounce<T extends (...args: any[]) => any>(fn: T, delay: number): DebouncedFunction<T> {
    let timeoutId: ReturnType<typeof setTimeout> | null = null;

    const debounced: DebouncedFunction<T> = function (this: any, ...args: Parameters<T>) {
        if (timeoutId) {
            clearTimeout(timeoutId);
        }

        timeoutId = setTimeout(() => {
            fn.apply(this, args);
            timeoutId = null;
        }, delay);
    } as any;

    debounced.cancel = () => {
        if (timeoutId) {
            clearTimeout(timeoutId);
            timeoutId = null;
        }
    };

    debounced.flush = function (this: any, ...args: Parameters<T>) {
        debounced.cancel();
        fn.apply(this, args);
    } as any;

    return debounced;
}
