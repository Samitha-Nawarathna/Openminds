export function getLowSaturatedColor() {
  // random hue for variety
  const h = Math.floor(Math.random() * 360);
  // softer saturation (15–35%)
  const s = 15 + Math.random() * 20;
  // lighter tones (75–90%)
  const l = 75 + Math.random() * 15;
  
    // convert HSL → RGB → HEX
    return hslToHex(h, s, l);
  }
  
  export function hslToHex(h, s, l) {
    s /= 100;
    l /= 100;
    const k = n => (n + h / 30) % 12;
    const a = s * Math.min(l, 1 - l);
    const f = n => l - a * Math.max(-1, Math.min(k(n) - 3, Math.min(9 - k(n), 1)));
    const toHex = x => Math.round(x * 255).toString(16).padStart(2, '0');
    return `#${toHex(f(0))}${toHex(f(8))}${toHex(f(4))}`;
  }