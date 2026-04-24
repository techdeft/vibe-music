// API service - reads config injected by WordPress (VibeConfig) or falls back to dev defaults
const config = window.VibeConfig || {
  apiBase: 'http://billpayment.local/wp-json/vibe-music/v1',
  nonce: '',
  playerName: 'VIBE',
  tagline: 'Live the Sound',
  primaryColor: '#FF0000',
}

let currentNonce = config.nonce || ''

function getHeaders(json = true) {
  const h = {}
  if (json) h['Content-Type'] = 'application/json'
  if (currentNonce) h['X-WP-Nonce'] = currentNonce
  return h
}

async function get(path, params = {}) {
  const url = new URL(config.apiBase + path)
  Object.keys(params).forEach(k => url.searchParams.set(k, params[k]))
  const res = await fetch(url.toString(), { headers: getHeaders(false), credentials: 'include' })
  if (!res.ok) {
    const err = await res.json().catch(() => ({}))
    throw new Error(err.message || `API error: ${res.status}`)
  }
  return res.json()
}

async function post(path, body = {}) {
  const res = await fetch(config.apiBase + path, {
    method: 'POST',
    headers: getHeaders(),
    credentials: 'include',
    body: JSON.stringify(body),
  })
  const data = await res.json().catch(() => ({}))
  if (!res.ok) throw new Error(data.message || `API error: ${res.status}`)
  return data
}

async function put(path, body = {}) {
  const res = await fetch(config.apiBase + path, {
    method: 'PUT',
    headers: getHeaders(),
    credentials: 'include',
    body: JSON.stringify(body),
  })
  const data = await res.json().catch(() => ({}))
  if (!res.ok) throw new Error(data.message || `API error: ${res.status}`)
  return data
}

async function del(path, body = {}) {
  const res = await fetch(config.apiBase + path, {
    method: 'DELETE',
    headers: getHeaders(),
    credentials: 'include',
    body: JSON.stringify(body),
  })
  const data = await res.json().catch(() => ({}))
  if (!res.ok) throw new Error(data.message || `API error: ${res.status}`)
  return data
}

export const api = {
  config,

  updateNonce(nonce) {
    currentNonce = nonce
  },

  // --- Music ---
  discovery: () => get('/discovery'),
  artists: (params) => get('/artists', params),
  artist: (id) => get(`/artist/${id}`),
  albums: (params) => get('/albums', params),
  album: (id) => get(`/album/${id}`),
  tracks: (params) => get('/tracks', params),
  genres: () => get('/genres'),
  genreTracks: (slug) => get(`/genre/${slug}/tracks`),
  search: (q) => get('/search', { q }),
  trackStream: (id) => post(`/track/${id}/stream`),

  // --- Auth ---
  authMe: () => get('/auth/me'),
  authLogin: (username, password) => post('/auth/login', { username, password }),
  authRegister: (username, email, password, display_name) =>
    post('/auth/register', { username, email, password, display_name }),
  authLogout: () => post('/auth/logout'),
  authForgotPassword: (email) => post('/auth/forgot-password', { email }),

  // --- Playlists ---
  getPlaylists: () => get('/playlists'),
  getPlaylist: (id) => get(`/playlists/${id}`),
  createPlaylist: (name, description, isPublic) =>
    post('/playlists', { name, description, public: isPublic }),
  updatePlaylist: (id, data) => put(`/playlists/${id}`, data),
  deletePlaylist: (id) => del(`/playlists/${id}`),
  addTrackToPlaylist: (id, track_id) => post(`/playlists/${id}/tracks`, { track_id }),
  removeTrackFromPlaylist: (id, track_id) => del(`/playlists/${id}/tracks`, { track_id }),

  // --- Likes ---
  likeTrack: (id) => post(`/tracks/${id}/like`),
  getLikedSongs: () => get('/me/liked-songs'),
}

