// API service - reads config injected by WordPress (VibeConfig) or falls back to dev defaults
const config = window.VibeConfig || {
  apiBase: 'http://billpayment.local/wp-json/vibe-music/v1',
  nonce: '',
  playerName: 'VIBE',
  tagline: 'Live the Sound',
  primaryColor: '#FF0000',
}

const headers = {
  'Content-Type': 'application/json',
  ...(config.nonce ? { 'X-WP-Nonce': config.nonce } : {}),
}

async function get(path, params = {}) {
  const url = new URL(config.apiBase + path)
  Object.keys(params).forEach(k => url.searchParams.set(k, params[k]))
  const res = await fetch(url.toString(), { headers })
  if (!res.ok) throw new Error(`API error: ${res.status}`)
  return res.json()
}

export const api = {
  config,

  discovery: () => get('/discovery'),

  artists: (params) => get('/artists', params),
  artist: (id) => get(`/artist/${id}`),

  albums: (params) => get('/albums', params),
  album: (id) => get(`/album/${id}`),

  tracks: (params) => get('/tracks', params),

  genres: () => get('/genres'),
  genreTracks: (slug) => get(`/genre/${slug}/tracks`),

  search: (q) => get('/search', { q }),
}
