import {ref} from "vue";
import type {InventoryItem} from "@/features/pantry/types/inventry.ts";
import {listInventoryItems} from "@/features/pantry/services/inventory.service.ts";


export function useInventoryList () {
    const loading = ref<boolean>(false);
    const error = ref<string | null>(null);

    const searchQuery = ref<string>('');
    const searchTimer = ref<number | null>(null);
    const inventory = ref<InventoryItem[]>([]);

    async function getInventoryItems(search?: string) {
        loading.value = true;
        error.value = null;

        try {
            inventory.value = await listInventoryItems(search ?? searchQuery.value, 100);
        } catch {
            error.value = "Error retrieving inventory."
        } finally {
            loading.value = false;
        }
    }

    function onSearchInput(value: string) {
        searchQuery.value = value;

        if (searchTimer.value !== null) {
            window.clearTimeout(searchTimer.value);
        }

        searchTimer.value = window.setTimeout(async () => {
            await getInventoryItems(searchQuery.value);
        }, 250);
    }

    function clearTimer() {
        if (searchTimer.value !== null) {
            window.clearTimeout(searchTimer.value);
        }
    }

    return  {
        loading,
        error,
        searchQuery,
        getInventoryItems,
        onSearchInput,
        clearTimer,
        inventory
    }
}
