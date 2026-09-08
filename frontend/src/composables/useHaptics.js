export function useHaptics() {
  const isSupported = 'vibrate' in navigator

  const vibrateRun = () => {
    if (isSupported) navigator.vibrate([100, 100, 100])
  }

  const vibrateWalk = () => {
    if (isSupported) navigator.vibrate([300])
  }

  return { isSupported, vibrateRun, vibrateWalk }
}