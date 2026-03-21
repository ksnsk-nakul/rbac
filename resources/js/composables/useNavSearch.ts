import { ref } from 'vue';

// Shared nav search state used by header + sidebar.
export const navSearchQuery = ref('');

export function setNavSearchQuery(value: string) {
    navSearchQuery.value = value;
}

