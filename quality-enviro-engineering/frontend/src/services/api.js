// Thin REST client for the PHP API with a short in-memory cache for GETs.
const BASE = (import.meta.env.VITE_API_BASE || '/api').replace(/\/$/, '');
const TTL = 60 * 1000;
const cache = new Map();

export class ApiError extends Error {
  constructor(message, status, errors) {
    super(message);
    this.status = status;
    this.errors = errors || {};
  }
}

async function request(path, options = {}) {
  let res;
  try {
    res = await fetch(BASE + path, {
      headers: { Accept: 'application/json', ...(options.body ? { 'Content-Type': 'application/json' } : {}) },
      credentials: 'same-origin',
      ...options,
    });
  } catch {
    throw new ApiError('Unable to reach the server. Please check your connection and try again.', 0);
  }
  let json = null;
  try {
    json = await res.json();
  } catch {
    /* non-JSON response */
  }
  if (!res.ok || !json || json.success === false) {
    throw new ApiError(json?.message || `Request failed (${res.status})`, res.status, json?.errors);
  }
  return json;
}

export async function get(path) {
  const hit = cache.get(path);
  if (hit && Date.now() - hit.t < TTL) return hit.data;
  const json = await request(path);
  cache.set(path, { t: Date.now(), data: json.data });
  return json.data;
}

export function peek(path) {
  const hit = cache.get(path);
  return hit && Date.now() - hit.t < TTL ? hit.data : undefined;
}

export function prime(path, data) {
  cache.set(path, { t: Date.now(), data });
}

export async function post(path, body) {
  return request(path, { method: 'POST', body: JSON.stringify(body) });
}

export const api = {
  bootstrap: () => get('/bootstrap'),
  home: () => get('/home'),
  page: (key) => get(`/pages/${encodeURIComponent(key)}`),
  services: () => get('/services'),
  service: (slug) => get(`/services/${encodeURIComponent(slug)}`),
  posts: (page = 1, limit = 9) => get(`/posts?page=${page}&limit=${limit}`),
  post: (slug) => get(`/posts/${encodeURIComponent(slug)}`),
  contact: (data) => post('/contact', data),
  quote: (data) => post('/quote', data),
};
