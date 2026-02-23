import {defineStore} from 'pinia';

export interface FeedbackState {
    type: 'success' | 'error';
    message: string;
}

interface ScopedFeedbackState {
    entries: Record<string, FeedbackState>;
}

export const useFeedbackStore = defineStore('feedback', {
    state: (): ScopedFeedbackState => ({
        entries: {}
    }),
    getters: {
        forScope: (state) => (scope: string): FeedbackState | null => {
            return state.entries[scope] ?? null;
        }
    },
    actions: {
        set(scope: string, feedback: FeedbackState) {
            this.entries[scope] = feedback;
        },
        success(scope: string, message: string) {
            this.set(scope, {type: 'success', message});
        },
        error(scope: string, message: string) {
            this.set(scope, {type: 'error', message});
        },
        clear(scope: string) {
            if (!this.entries[scope]) {
                return;
            }

            delete this.entries[scope];
        },
    }
});
