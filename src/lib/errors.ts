import axios from "axios";
import type {ApiErrorResponse} from "@/lib/api.ts";

export function getFirstValidationError(errors?: Record<string, string[]>) {
    return errors
        ? Object.values(errors).flat().find((message) => message.length > 0)
        : undefined;
}

export function getApiErrorMessage(error: unknown, fallback: string) {
    if (!axios.isAxiosError<ApiErrorResponse>(error)) {
        return fallback;
    }

    const firstValidationError = getFirstValidationError(error.response?.data?.errors);
    return firstValidationError ?? error.response?.data?.message ?? fallback;
}
