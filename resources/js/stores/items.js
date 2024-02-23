import { defineStore } from 'pinia';

export const useItemsStore = defineStore("items", {
    state: ()=> {
        return { items: Array }
    },
    actions: {
        addArray(arr) {
            this.items = arr;
        }
    }
})