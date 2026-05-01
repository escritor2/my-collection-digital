/// <reference types="nuxt" />
/// <reference types="nuxt/schema" />
/// <reference types="node" />

declare module 'pdfjs-dist/build/pdf.worker.mjs?url' {
  const workerUrl: string;
  export default workerUrl;
}

