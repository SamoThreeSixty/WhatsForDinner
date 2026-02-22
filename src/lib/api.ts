import axios from "axios";

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL;

export const api = axios.create({
    baseURL: `${apiBaseUrl}/api`,
    headers: {
        'Content-Type': 'application/json',
    },
    withCredentials: true,
})

