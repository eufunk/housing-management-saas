import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

/**
 * Debounced text search bound to an index route's `search` query param.
 * Extra filters (e.g. a status dropdown) can be merged in via `extraParams`,
 * which is re-read on every navigation so callers can pass a reactive getter.
 */
export function useListSearch(routeName: string, initialSearch: string | null, extraParams: () => Record<string, string | null> = () => ({})) {
    const search = ref(initialSearch ?? '');
    let timeout: ReturnType<typeof setTimeout> | undefined;

    const navigate = () => {
        router.get(
            route(routeName),
            { search: search.value || undefined, ...extraParams() },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    watch(search, () => {
        clearTimeout(timeout);
        timeout = setTimeout(navigate, 300);
    });

    return { search, navigate };
}
