import { defineConfig } from 'vitepress'

// https://vitepress.dev/reference/site-config
export default defineConfig({
  lang: 'en-US',
  title: 'Cubeta Starter',
  description:
    "Cubeta-Starter: A developer's Swiss army knife for seamless CRUD operations. " +
    'Enjoy a user-friendly GUI for code generation, enhancing your development workflow. ' +
    'Say goodbye to repetition, embrace productivity with Cubeta-Starter.',

  // Served on GitHub Pages at https://cubeta-io.github.io/cubeta-starter/
  base: '/cubeta-starter/',

  // Auto light/dark following system preference, with a manual toggle.
  appearance: true,
  lastUpdated: true,
  cleanUrls: true,
  ignoreDeadLinks: true,

  head: [
    ['link', { rel: 'icon', type: 'image/png', href: '/cubeta-starter/cubeta-logo.png' }],
    ['meta', { name: 'theme-color', content: '#165F96' }],
    ['meta', { property: 'og:type', content: 'website' }],
    ['meta', { property: 'og:title', content: 'Cubeta Starter' }],
    [
      'meta',
      {
        property: 'og:description',
        content: 'Accelerate your Laravel development with automated CRUD generation.',
      },
    ],
  ],

  markdown: {
    lineNumbers: true,
  },

  themeConfig: {
    // https://vitepress.dev/reference/default-theme-config
    logo: '/cubeta-logo.png',
    siteTitle: 'Cubeta Starter',

    nav: [
      { text: 'Introduction', link: '/introduction' },
      { text: 'Quickstart', link: '/quickstart' },
      { text: 'Commands', link: '/commands' },
      { text: 'Usage', link: '/usage' },
      { text: 'Changelog', link: '/_changelog' },
      { text: 'Cubeta', link: 'https://cubeta.io/' },
    ],

    sidebar: [
      {
        text: 'Getting Started',
        collapsed: false,
        items: [
          { text: 'Introduction', link: '/introduction' },
          { text: 'Quickstart', link: '/quickstart' },
          { text: 'Installation', link: '/installation' },
          { text: 'Configuration', link: '/configuration' },
          { text: 'Features', link: '/features' },
        ],
      },
      {
        text: 'Usage Guide',
        collapsed: false,
        items: [
          { text: 'Basic Usage', link: '/usage' },
          { text: 'Commands Reference', link: '/commands' },
          { text: 'Published Files', link: '/published-files' },
          { text: 'Generated Files', link: '/created-files' },
          { text: 'Models', link: '/created-model' },
          { text: 'Roles & Permissions', link: '/permissions-usage' },
        ],
      },
      {
        text: 'Core Components',
        collapsed: false,
        items: [
          { text: 'BaseRepository class', link: '/base-repository' },
          { text: 'BaseService class', link: '/base-service' },
          { text: 'BaseResource class', link: '/base-resource' },
          { text: 'ApiResponse class', link: '/api-response' },
          { text: 'BaseExporter class', link: '/base-exporter' },
          { text: 'BaseImporter class', link: '/base-importer' },
          { text: 'BaseBulkAction class', link: '/base-bulk-action' },
          { text: 'Testing', link: '/main-test' },
          { text: 'Translations', link: '/translatable-serializer' },
        ],
      },
      {
        text: 'Help & Support',
        collapsed: false,
        items: [
          { text: 'Troubleshooting', link: '/troubleshooting' },
          { text: 'Contributing', link: '/contributing' },
          { text: 'Changelog', link: '/_changelog' },
        ],
      },
    ],

    socialLinks: [{ icon: 'github', link: 'https://github.com/cubeta-io/cubeta-starter' }],

    search: {
      provider: 'local',
    },

    editLink: {
      pattern: 'https://github.com/cubeta-io/cubeta-starter/edit/main/docs/:path',
      text: 'Edit this page on GitHub',
    },

    footer: {
      message: 'Released under the MIT License.',
      copyright: 'Developed with ❤️ by Cubeta',
    },
  },
})
