import { h, defineComponent } from 'vue';

type SizeProp = string | number;

const makeProps = () => ({
  width: { type: [String, Number] as () => SizeProp, default: 24 },
  height: { type: [String, Number] as () => SizeProp, default: 24 },
});

// Bitcoin
export const BitcoinIcon = defineComponent({
  name: 'BitcoinIcon',
  props: makeProps(),
  setup(props) {
    return () =>
      h('svg', {
        width: props.width,
        height: props.height,
        viewBox: '0 0 24 24',
        fill: 'none',
        xmlns: 'http://www.w3.org/2000/svg',
      }, [
        h('path', { d: 'M12 24c6.627 0 12-5.373 12-12S18.627 0 12 0 0 5.373 0 12s5.373 12 12 12z', fill: '#F7931A' }),
        h('path', { d: 'M17.3 10.7c.3-2-1.2-3.1-3.3-3.8l.7-2.7-1.6-.4-.7 2.6-1.3-.3.7-2.6-1.6-.4-.7 2.7-.9-.2v-.1L7 5.3l-.4 1.7s1.2.3 1.2.3c.7.2.8.7.8 1.1l-.8 3.2-.9 3.6c-.1.2-.3.6-.8.4 0 0-1.2-.3-1.2-.3L4 17l1.4.4 1.5.4-.7 2.7 1.6.4.7-2.7 1.3.3-.7 2.7 1.6.4.7-2.7c2.7.5 4.7.3 5.6-2.1.7-1.9 0-3.1-1.5-3.8 1.1-.2 1.9-1 2.1-2.3zm-3.8 5c-.5 1.9-3.9.9-5 .6l.9-3.5c1.1.3 4.6.9 4.1 2.9zm.5-5c-.5 1.7-3.3.8-4.3.6l.8-3.2c.9.2 3.9.7 3.5 2.6z', fill: 'white' })
      ]);
  }
});

// Ethereum
export const EthereumIcon = defineComponent({
  name: 'EthereumIcon',
  props: makeProps(),
  setup(props) {
    return () =>
      h('svg', {
        width: props.width,
        height: props.height,
        viewBox: '0 0 24 24',
        fill: 'none',
        xmlns: 'http://www.w3.org/2000/svg',
      }, [
        h('path', { d: 'M12 24c6.627 0 12-5.373 12-12S18.627 0 12 0 0 5.373 0 12s5.373 12 12 12z', fill: '#627EEA' }),
        h('path', { d: 'M12.374 3v6.652l5.623 2.513L12.374 3z', fill: 'white', 'fill-opacity': '0.6' }),
        h('path', { d: 'M12.374 3L6.75 12.165l5.624-2.513V3z', fill: 'white' }),
        h('path', { d: 'M12.374 16.476v4.52L18 13.212l-5.626 3.264z', fill: 'white', 'fill-opacity': '0.6' }),
        h('path', { d: 'M12.374 20.996v-4.52L6.75 13.212l5.624 7.784z', fill: 'white' }),
      ]);
  }
});

// Ripple (XRP)
export const RippleIcon = defineComponent({
  name: 'RippleIcon',
  props: makeProps(),
  setup(props) {
    return () =>
      h('svg', {
        width: props.width,
        height: props.height,
        viewBox: '0 0 24 24',
        fill: 'none',
        xmlns: 'http://www.w3.org/2000/svg',
      }, [
        h('path', { d: 'M12 24c6.627 0 12-5.373 12-12S18.627 0 12 0 0 5.373 0 12s5.373 12 12 12z', fill: '#23292F' }),
        h('path', { d: 'M17.084 15.846c-.487.483-1.005.915-1.55 1.293a8.339 8.339 0 01-1.73.91 8.072 8.072 0 01-1.867.484 8.524 8.524 0 01-3.876-.438 8.133 8.133 0 01-1.73-.91 8.448 8.448 0 01-1.55-1.293l1.32-1.293c.38.376.784.72 1.212.968.428.249.879.461 1.348.614a6.63 6.63 0 001.444.308c.498.044.998.044 1.496 0a6.62 6.62 0 001.444-.308c.47-.153.92-.365 1.348-.614.428-.248.832-.592 1.212-.968l1.32 1.293zm0-7.692l-1.32 1.293a5.168 5.168 0 00-1.212.968c-.428.249-.879.461-1.348.614a6.63 6.63 0 01-1.444.308 7.104 7.104 0 01-1.496 0 6.63 6.63 0 01-1.444-.308 6.258 6.258 0 01-1.348-.614 5.168 5.168 0 00-1.212-.968L5.33 8.154c.487-.483 1.005-.915 1.55-1.293a8.339 8.339 0 011.73-.91 8.072 8.072 0 011.867-.484c1.298-.18 2.62-.083 3.876.438.62.243 1.2.544 1.73.91.545.378 1.063.81 1.55 1.293z', fill: 'white' })
      ]);
  }
});

// Cardano
export const CardanoIcon = defineComponent({
  name: 'CardanoIcon',
  props: makeProps(),
  setup(props) {
    return () =>
      h('svg', {
        width: props.width,
        height: props.height,
        viewBox: '0 0 24 24',
        fill: 'none',
        xmlns: 'http://www.w3.org/2000/svg',
      }, [
        h('path', { d: 'M12 24c6.627 0 12-5.373 12-12S18.627 0 12 0 0 5.373 0 12s5.373 12 12 12z', fill: '#0033AD' }),
        h('path', { d: 'M12 4.5l1.5 1.5-1.5 1.5L10.5 6 12 4.5zm3 3l1.5 1.5-1.5 1.5L13.5 9 15 7.5zm-6 0L10.5 9 9 10.5 7.5 9 9 7.5zm9 3l1.5 1.5-1.5 1.5-1.5-1.5 1.5-1.5zm-12 0l1.5 1.5L6 13.5 4.5 12 6 10.5zm9 3l1.5 1.5-1.5 1.5-1.5-1.5 1.5-1.5zm-6 0l1.5 1.5L9 16.5 7.5 15 9 13.5zm3 3l1.5 1.5-1.5 1.5-1.5-1.5 1.5-1.5z', fill: 'white' })
      ]);
  }
});

// Bitcoin Cash
export const BitcoinCashIcon = defineComponent({
  name: 'BitcoinCashIcon',
  props: makeProps(),
  setup(props) {
    return () =>
      h('svg', {
        width: props.width,
        height: props.height,
        viewBox: '0 0 24 24',
        fill: 'none',
        xmlns: 'http://www.w3.org/2000/svg',
      }, [
        h('path', { d: 'M12 24c6.627 0 12-5.373 12-12S18.627 0 12 0 0 5.373 0 12s5.373 12 12 12z', fill: '#8DC351' }),
        h('path', { d: 'M15.84 10.92c-.28-1.47-1.7-1.57-3.22-1.45l-.55-2.2-1.33.33.54 2.15c-.35.09-.7.18-1.05.27l-.54-2.17-1.33.33.54 2.2c-.29.07-.57.15-.84.22l-.01-.01-1.83.46.35 1.43s.98-.31 .97-.29c.54-.13.8.11.92.37l1.01 4.04c.05.13.02.35-.34.43 .02.01-.98.24-.98.24l.2 1.63 1.73-.43c.32-.08.64-.16.95-.24l.55 2.22 1.33-.33-.55-2.19c.36-.09.72-.18 1.06-.27l.54 2.17 1.33-.33-.55-2.21c2.22-.42 3.88-1.13 3.45-3.61-.35-2.01-1.4-2.61-2.82-2.55.69-.64.99-1.51.53-2.68zm-.83 5.5c.5 2.01-3.07 2.24-4.11 2.5l-.73-2.93c1.04-.26 4.31-1.29 4.84.43zm-1.9-3.94c.46 1.83-2.54 2.02-3.4 2.24l-.67-2.66c.86-.21 3.58-1.13 4.07.42z', fill: 'white' })
      ]);
  }
});

// Litecoin
export const LitecoinIcon = defineComponent({
  name: 'LitecoinIcon',
  props: makeProps(),
  setup(props) {
    return () =>
      h('svg', {
        width: props.width,
        height: props.height,
        viewBox: '0 0 24 24',
        fill: 'none',
        xmlns: 'http://www.w3.org/2000/svg',
      }, [
        h('path', { d: 'M12 24c6.627 0 12-5.373 12-12S18.627 0 12 0 0 5.373 0 12s5.373 12 12 12z', fill: '#345D9D' }),
        h('path', { d: 'M7.5 14.5l-1 3.5h10l.5-2L9.5 16l1.5-5.5 2-7-3 1-1.5 5.5L7 14.5z', fill: 'white' })
      ]);
  }
});

// NEM
export const NEMIcon = defineComponent({
  name: 'NEMIcon',
  props: makeProps(),
  setup(props) {
    return () =>
      h('svg', {
        width: props.width,
        height: props.height,
        viewBox: '0 0 24 24',
        fill: 'none',
        xmlns: 'http://www.w3.org/2000/svg',
      }, [
        h('path', { d: 'M12 24c6.627 0 12-5.373 12-12S18.627 0 12 0 0 5.373 0 12s5.373 12 12 12z', fill: '#67B2E8' }),
        h('path', { d: 'M16.5 7.5l-9 9M7.5 7.5h9v9', stroke: 'white', 'stroke-width': '2' })
      ]);
  }
});

// Stellar
export const StellarIcon = defineComponent({
  name: 'StellarIcon',
  props: makeProps(),
  setup(props) {
    return () =>
      h('svg', {
        width: props.width,
        height: props.height,
        viewBox: '0 0 24 24',
        fill: 'none',
        xmlns: 'http://www.w3.org/2000/svg',
      }, [
        h('path', { d: 'M12 24c6.627 0 12-5.373 12-12S18.627 0 12 0 0 5.373 0 12s5.373 12 12 12z', fill: '#08B5E5' }),
        h('path', { d: 'M12 5l2 5h5l-4 3 2 5-5-3-5 3 2-5-4-3h5l2-5z', fill: 'white' })
      ]);
  }
});

// IOTA
export const IOTAIcon = defineComponent({
  name: 'IOTAIcon',
  props: makeProps(),
  setup(props) {
    return () =>
      h('svg', {
        width: props.width,
        height: props.height,
        viewBox: '0 0 24 24',
        fill: 'none',
        xmlns: 'http://www.w3.org/2000/svg',
      }, [
        h('path', { d: 'M12 24c6.627 0 12-5.373 12-12S18.627 0 12 0 0 5.373 0 12s5.373 12 12 12z', fill: '#242424' }),
        h('path', { d: 'M12 7v10M9 8.5v7M15 8.5v7', stroke: 'white', 'stroke-width': '2', 'stroke-linecap': 'round' })
      ]);
  }
});

// Dash
export const DashIcon = defineComponent({
  name: 'DashIcon',
  props: makeProps(),
  setup(props) {
    return () =>
      h('svg', {
        width: props.width,
        height: props.height,
        viewBox: '0 0 24 24',
        fill: 'none',
        xmlns: 'http://www.w3.org/2000/svg',
      }, [
        h('path', { d: 'M12 24c6.627 0 12-5.373 12-12S18.627 0 12 0 0 5.373 0 12s5.373 12 12 12z', fill: '#1C75BC' }),
        h('path', { d: 'M7 8h5l-1 3h5l-1 3H7l1-3H6l1-3zm2 8h8l-1-2h-8l1 2z', fill: 'white' })
      ]);
  }
});