import { useCallback, useEffect, useState } from 'react';
import { peek } from '../services/api';

/**
 * Run an async API loader and track { data, loading, error }.
 * `cacheKey` lets the hook render cached data instantly (no loading flash on back-navigation).
 */
export function useApi(loader, deps = [], cacheKey) {
  const cached = cacheKey ? peek(cacheKey) : undefined;
  const [state, setState] = useState({ data: cached ?? null, loading: cached === undefined, error: null });
  const [nonce, setNonce] = useState(0);

  useEffect(() => {
    let alive = true;
    const instant = cacheKey ? peek(cacheKey) : undefined;
    setState({ data: instant ?? null, loading: instant === undefined, error: null });
    loader()
      .then((data) => alive && setState({ data, loading: false, error: null }))
      .catch((error) => alive && setState({ data: null, loading: false, error }));
    return () => {
      alive = false;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [...deps, nonce]);

  const retry = useCallback(() => setNonce((n) => n + 1), []);
  return { ...state, retry };
}
