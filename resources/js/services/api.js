const API_BASE = '/api/v1';

const getToken = () => localStorage.getItem('auth_token');

const headers = (includeAuth = true) => {
    const h = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    };

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (csrfToken) {
        h['X-CSRF-TOKEN'] = csrfToken;
    }

    if (includeAuth) {
        const token = getToken();
        if (token) {
            h['Authorization'] = `Bearer ${token}`;
        }
    }

    return h;
};

const handleResponse = async (response) => {
    const data = await response.json();

    if (!response.ok) {
        if (response.status === 401) {
            localStorage.removeItem('auth_token');
            window.location.href = '/login';
        }
        throw new Error(data.message || 'An error occurred');
    }

    return data;
};

export const api = {
    async get(endpoint) {
        const response = await fetch(`${API_BASE}${endpoint}`, {
            method: 'GET',
            headers: headers(),
        });
        return handleResponse(response);
    },

    async post(endpoint, body = {}) {
        const response = await fetch(`${API_BASE}${endpoint}`, {
            method: 'POST',
            headers: headers(),
            body: JSON.stringify(body),
        });
        return handleResponse(response);
    },

    async put(endpoint, body = {}) {
        const response = await fetch(`${API_BASE}${endpoint}`, {
            method: 'PUT',
            headers: headers(),
            body: JSON.stringify(body),
        });
        return handleResponse(response);
    },

    async delete(endpoint) {
        const response = await fetch(`${API_BASE}${endpoint}`, {
            method: 'DELETE',
            headers: headers(),
        });
        return handleResponse(response);
    },
};

export const auth = {
    async login(email, password) {
        const response = await fetch(`${API_BASE}/auth/login`, {
            method: 'POST',
            headers: headers(false),
            body: JSON.stringify({ email, password }),
        });

        const data = await handleResponse(response);
        localStorage.setItem('auth_token', data.token);
        return data;
    },

    async logout() {
        try {
            await api.post('/auth/logout');
        } finally {
            localStorage.removeItem('auth_token');
        }
    },

    async getUser() {
        return api.get('/auth/user');
    },

    isAuthenticated() {
        return !!getToken();
    },
};

export default api;
