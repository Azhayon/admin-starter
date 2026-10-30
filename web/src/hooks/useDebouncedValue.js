import { useEffect, useState } from "react"

export function useDebouncedValue(value, delay = 300) {
  const [debounced, setDebounced] = useState(value)

  useEffect(() => {
    const id = setTimeout(() => setDebounced(value), delay)
    return () => clearTimeout(id) // cleanup: a new keystroke cancels the pending update
  }, [value, delay])

  return debounced
}