export const MatrixShader = {
  uniforms: {
    time: { value: 0 },
    resolution: { value: [0, 0] },
    opacity: { value: 0.5 }
  },
  vertexShader: `
    varying vec2 vUv;
    void main() {
      vUv = uv;
      gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0);
    }
  `,
  fragmentShader: `
    uniform float time;
    uniform float opacity;
    varying vec2 vUv;

    float random(vec2 st) {
      return fract(sin(dot(st.xy, vec2(12.9898,78.233))) * 43758.5453123);
    }

    void main() {
      vec2 st = vUv;
      float r = random(vec2(st.x, floor(st.y * 50.0) + time));
      float g = step(0.98, r);
      vec3 color = vec3(0.0, g * 0.7, 0.0);
      gl_FragColor = vec4(color, opacity);
    }
  `
};
