import { defineConfig, mergeConfig } from 'vitepress'
import { tabsMarkdownPlugin } from 'vitepress-plugin-tabs'

const settings = {
};

if (process.env.NODE_ENV === 'production') {
} else {
  settings.markdown = {
    lineNumbers: true,
  };
}

// https://vitepress.dev/reference/site-config
export default mergeConfig(settings, defineConfig({
  lang: 'ko-KR',
  title: "라이믹스 리액션 모듈",
  description: "라이믹스 리액션 모듈",
  titleTemplate: ':title - RX:DA Reaction',
  base: '/module-da_reaction',
  themeConfig: {
    // https://vitepress.dev/reference/default-theme-config
    siteTitle: 'DA Reaction',
    outline: [2, 4],
    nav: [
      { text: 'Home', link: '/' },
    ],

    sidebar: [
      {
        text: '기본',
        items: [
          { text: '살펴보기', link: '/basic/about' },
          { text: '설치 및 설정하기', link: '/basic/install' },
          { text: '리액션 버튼 추가하기', link: '/basic/install-button' },
          { text: '이모티콘 이미지 추가하기', link: '/basic/import-emoticon' },
        ]
      },
      {
        text: '커스터마이징',
        items: [
          { text: '이모티콘 추가하기', link: '/advanced/add-emoticons' },
          { text: '리액션 버튼', link: '/advanced/customize-button' },
          { text: '프론트엔드', link: '/advanced/front-end' },
          // { text: '모듈 설정' },
          // { text: '템플릿 변경하기' },
        ]
      },
    ],

    externalLinkIcon: true,

    editLink: {
      pattern: 'https://github.com/rhymix-guide/module-da_reaction/edit/main/docs/:path',
      text: '이 페이지 수정하기'
    },

    docFooter: {
      prev: '이전',
      next: '다음'
    },

    // 검색
    search: {
      provider: 'local'
    },

    // 마지막 업데이트
    lastUpdated: {
      formatOptions: {
        dateStyle: 'long',
        timeStyle: undefined,
      }
    },

    // 소셜 링크
    socialLinks: [
      { icon: 'github', link: 'https://github.com/rhymix-guide/module-da_reaction' },
    ],
  },

  markdown: {
    config(md) {
      md.use(tabsMarkdownPlugin)
    }
  },

  sitemap: {
    hostname: 'https://rhymix-guide.github.io/module-da_reaction/',
  },
}))
