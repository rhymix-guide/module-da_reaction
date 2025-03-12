'use strict';

/**
 * @typedef {Object} ReactionData
 * @property {string} category 카테고리
 * @property {string} reactionId 카테고리가 제거된 아이디
 * @property {?string} title
 * @property {?string} description
 * @property {?string} credit
 * @property {string} renderType
 * @property {?string} url
 * @property {?string} alias 대체 할 다른 리액션 ID
 *
 * @typedef {Object} Emoticon
 * @property {string} reaction
 * @property {string} category
 * @property {string} renderType
 * @property {string} reactionId
 * @property {?string} emoji
 * @property {?string} url
 *
 * @typedef {Object} ReactionContainerOptions
 * @property {boolean|'start'|'end'} button react 버튼 표시여부 또는 위치
 * @property {'prepend'|'append'} placement 리액션 이모티콘을 삽입할 위치
 *
 * @typedef {Object} ReactionOptions
 * @property {boolean} reactable 리액션 가능 여부
 * @property {number} reactionLimit 리액션 회숫 제한
 */

document.addEventListener('alpine:init', function () {
  /**
   * 공유 저장소
   */
  Alpine.store('reactionData', {
    /** @type {ReactionOptions} */
    options: {
      reactable: true,
      reactionLimit: 20,
    },
    /** @type {boolean} */
    modalOpen: false,
    /** @type {array} */
    categories: [],
    /** @type {Emoticon[]} */
    emoticons: [],
    /** @type {object} */
    alias: {},
    /** @type {array} */
    reactions: [],
    /** @type {array} */
    _emoticonsIndex: [],
    /** @type {?string|number} */
    memberId: null,
    /** @type {boolean} */
    isAdmin: false,

    /**
     * 이모티콘 추가
     *
     * @param {Emoticon} data
     */
    addEmoticon(data) {
      if (!data.reaction) {
        return;
      }

      if (/(\u00a9|\u00ae|[\u2000-\u3300]|\ud83c[\ud000-\udfff]|\ud83d[\ud000-\udfff]|\ud83e[\ud000-\udfff])/gi.test(data.reaction)) {
        data.emoji = data.reaction;
        data.reaction = 'emoji:' + data.reaction.codePointAt(0).toString(16);
      }

      if (data.reaction.startsWith('emoji:')) {
        data.reaction = data.reaction.replace('emoji:U+', 'emoji:');
        if (!data.emoji) {
          data.emoji = String.fromCodePoint('0x' + data.reaction.split(':')[1]);
        }
      }

      // 이모티콘
      const store = Alpine.store('reactionData');
      const [category, ...rest] = data.reaction.split(':');
      data.category = category;
      data.reactionId = rest.join(':');
      data.renderType = store.categories.find(item => item.category === category).renderType;

      const existsIndex = this._emoticonsIndex.indexOf(data.reaction);
      if (existsIndex >= 0) {
        this.emoticons[existsIndex] = data;
      } else {
        this._emoticonsIndex.push(data.reaction);
        this.emoticons.push(data);
      }
    },
  });

  /**
   * 개별 리액션 컨테이너의 데이터
   *
   * @param {string} targetId
   * @param {ReactionContainerOptions} options
   */
  Alpine.data('daReaction', (targetId, options) => ({
    /** @type {ReactionContainerOptions} */
    options: {
      button: 'end',
      placement: 'prepend',
    },
    /** @type {string} 대상ID */
    targetId: null,
    /** @type {string} 대상의 상위ID 및 대상ID */
    parentId: null,
    /** @type {ReactionData[]} 리액션 목록 */
    reactions: [],

    init() {
      /*
      대상ID 설정
      `대상ID@상위ID` 형식으로 상위ID가 없으면 대상ID를 상위ID로 사용
      */
      const [target, parent = target] = targetId.split('@');
      this.targetId = target;
      this.parentId = parent;

      Object.assign(this.options, this.$store.reactionData.options, options);

      Alpine.effect(() => {
        this.reactions = this.$store.reactionData?.reactions[this.targetId] ?? {};
      })
    },

    /**
     * 이모티콘 데이터 반환
     *
     * @param {string} reaction 리액션 ID
     * @returns {ReactionData}
     */
    getEmoticon(reaction) {
      const store = Alpine.store('reactionData');

      const emoticon = store.emoticons.find(item => item.reaction === reaction) ?? {};

      // 이모티콘 대체
      const alias = store.alias[reaction] ?? false;
      if (alias) {
        const aliasReaction = store.emoticons.find(item => item.reaction === alias) ?? false;
        if (aliasReaction) {
          return aliasReaction;
        }
      }

      return emoticon;
    },

    /**
     * 리액션 목록
     */
    getReactions() {
      if (!this.reactions.length) {
        return [];
      }

      this.reactions.forEach(reaction => {
        reaction.emoticon = this.getEmoticon(reaction.reaction);
      });

      return this.reactions;
    },

    async add(reaction) {
      return await window.daReaction.react(reaction, 'add', this.targetId, this.parentId);
    },

    async toggle(reaction) {
      return await window.daReaction.react(reaction, 'toggle', this.targetId, this.parentId);
    }
  }))

  /**
   * 리액션 컨테이너 템플릿 출력을 위한 directive
   */
  Alpine.directive('da-reaction-print', (el, { expression }, { evaluate }) => {
    const options = evaluate('options');
    const placement = ['append', 'prepend'].includes(options.placement) ? options.placement : 'prepend';

    // TODO 템플릿 분리
    const template = `
        <div x-show="options.button && options.reactable" x-bind="daReactionButton" class="da-reaction__button" :class="{ 'da-reaction__button--end': options.button === 'end' }" title="리액션 남기기"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-emoji-smile" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/> <path d="M4.285 9.567a.5.5 0 0 1 .683.183A3.5 3.5 0 0 0 8 11.5a3.5 3.5 0 0 0 3.032-1.75.5.5 0 1 1 .866.5A4.5 4.5 0 0 1 8 12.5a4.5 4.5 0 0 1-3.898-2.25.5.5 0 0 1 .183-.683M7 6.5C7 7.328 6.552 8 6 8s-1-.672-1-1.5S5.448 5 6 5s1 .672 1 1.5m4 0c0 .828-.448 1.5-1 1.5s-1-.672-1-1.5S9.448 5 10 5s1 .672 1 1.5"/></svg></div>
        <template x-for="reaction in getReactions()" :key="reaction.reaction">
          <template x-if="reaction.count >= 1 && reaction.emoticon.renderType">
            <span role="button" tabindex="0" class="da-reaction__badge" :class="{'da-reaction__badge--choose': reaction.choose }" @click="toggle(reaction.reaction)" @keyup.enter="toggle(reaction.reaction)">
              <template x-if="reaction.emoticon.renderType === 'emoji'">
                <span class="da-reaction__emoticon da-reaction__emoji" x-text="reaction.emoticon.emoji"></span>
              </template>
              <template x-if="reaction.emoticon.renderType === 'image'">
                <span class="da-reaction__emoticon"><img :src="reaction.emoticon.url" loading="lazy" alt="" /></span>
              </template>
              <span class="da-reaction__count" x-text="reaction.count"></span>
            </span>
          </template>
        </template>
      `;

    el.insertAdjacentHTML(placement === 'append' ? 'beforeend' : 'afterbegin', template);
  })

  /**
   * 리액션 버튼
   */
  Alpine.bind('daReactionButton', (item = null) => ({
    'tabindex': 0,
    'role': 'button',
    '@click.stop'() {
      let targetId = this.targetId ?? item;
      let parentId = this.parentId ?? targetId;

      if (targetId.includes('@')) {
        const [target, parent = target] = (targetId).split('@');
        targetId = target;
        parentId = parent;
      }

      this.$dispatch('open-reaction-popover', { targetId: targetId, parentId: parentId });
    },
    '@keyup.enter'() {
      let targetId = this.targetId ?? item;
      let parentId = this.parentId ?? targetId;

      if (targetId.includes('@')) {
        const [target, parent = target] = (targetId).split('@');
        targetId = target;
        parentId = parent;
      }

      this.$dispatch('open-reaction-popover', { targetId: targetId, parentId: parentId });
    }
  }));

  /**
   * 리액션 선택 모달
   *
   * `open-reaction-modal` 이벤트를 받아 모달을 연다.
   */
  Alpine.data('reactionModal', () => ({
    /** @type {boolean} 모달 표시 여부 */
    open: false,
    /** @type {Emoticon[]} 이모티콘 목록 */
    emoticons: [],
    button: null,

    init() {
      document.addEventListener('open-reaction-modal', (e) => {
        this.open = true;
        this.emoticons = this.$store.reactionData.emoticons;
        this.targetId = e.detail.targetId;
        this.parentId = e.detail.parentId;
        this.button = e.target;

        const buttonRect = this.button.getBoundingClientRect();
        this.$refs.reactionModal.style.top = `${buttonRect.top + buttonRect.height + window.scrollY}px`;
        this.$refs.reactionModal.style.left = `${buttonRect.left + window.scrollX}px`;
      });
    },

    emoticonCountByCategory(category) {
      let count = 0;
      this.emoticons.map(item => { item.category === category ? ++count : null });
      return count;
    },

    react(reaction) {
      window.daReaction.react(reaction, 'add', this.targetId, this.parentId);
    },
  }));

  Alpine.data('daReactionPopover', () => ({
    /** @type {boolean} 모달 표시 여부 */
    open: false,
    /** @type {Emoticon[]} 이모티콘 목록 */
    emoticons: [],

    init() {
      document.addEventListener('open-reaction-popover', (e) => {
        this.open = true;
        this.emoticons = this.$store.reactionData.emoticons;//.filter(item => item.renderType === 'emoji');
        this.targetId = e.detail.targetId;
        this.parentId = e.detail.parentId;
        this.button = e.target;

        this.$refs.daReactionPopover.removeAttribute('aria-hidden');
        this.$refs.daReactionPopover.setAttribute('aria-modal', true);
        this.$refs.daReactionPopover.setAttribute('role', 'dialog');

        this.$nextTick(() => {
          this.position();
          this.$refs.daReactionPopover.focus();
        });
      });
    },

    hide() {
      this.open = false;
      this.$refs.daReactionPopover.setAttribute('aria-hidden', true);
      this.$refs.daReactionPopover.removeAttribute('aria-modal');
      this.$refs.daReactionPopover.removeAttribute('role');
    },

    position() {
      const buttonRect = this.button.getBoundingClientRect();
      const popoverWidth = this.$refs.daReactionPopover.offsetWidth;
      const windowWidth = window.innerWidth;

      let left = buttonRect.left + window.scrollX;
      if (left < 0) {
        left = 6;
      } else if (left + popoverWidth >= windowWidth) {
        left = windowWidth - popoverWidth - 6;
      }

      this.$refs.daReactionPopover.style.top = `${buttonRect.top + buttonRect.height + window.scrollY}px`;
      this.$refs.daReactionPopover.style.left = `${left}px`;
    },

    async react(reaction) {
      await window.daReaction.react(reaction, 'add', this.targetId, this.parentId);
      this.$nextTick(() => {
        const buttonRect = this.button.getBoundingClientRect();
        this.$refs.daReactionPopover.style.top = `${buttonRect.top + buttonRect.height + window.scrollY}px`;
      });
    },
  }));
});

/**
 * @typedef {object} EndPoints
 * @property {string} react 리액션
 *
 * @typedef {object} ReactRequestBody
 * @property {string} reaction
 * @property {string} reactionMode
 * @property {string} targetId
 * @property {string} parentId
 */
class DaReaction {
  /** @type {EndPoints} */
  endpoints = {};
  reactionIdList = [];
  store = null;

  constructor() {
    document.addEventListener('alpine:init', () => {
      this.store = Alpine.store('reactionData');

      // 리액션 초기화 이벤트
      document.dispatchEvent(new CustomEvent('daReaction:init', { cancelable: false, detail: this }));
    });
  }

  setMemberInfo(memberInfo) {
    this.store.memberId = memberInfo.memberId;
    this.store.isAdmin = memberInfo.isAdmin;
  }

  setOptions(options) {
    Object.assign(this.store.options, options);
  }

  /**
   * @param {EndPoints} endpoints
   */
  setEndpoints(endpoints) {
    this.endpoints = endpoints;
  }

  setReactions(reactions) {
    this.store.reactions = reactions;
  }

  addCategory(name, data) {
    data.category = name;
    this.store.categories.push(data);
  }

  addCategories(categories) {
    this.store.categories.push(...categories);
  }

  addEmoticons(emoticons) {
    emoticons.forEach(element => {
      this.store.addEmoticon(element);
    });
  }

  addAlias(alias) {
    this.store.alias = alias;
  }

  async react(reaction, reactionMode, targetId, parentId) {
    if (!this.store.memberId) {
      alert('로그인 후 이용 가능합니다.');
      return;
    }

    reactionMode = reactionMode ?? 'toggle';

    /** @type {ReactRequestBody} */
    const requestBody = {
      reaction,
      reactionMode,
      targetId,
      parentId,
      _rx_csrf_token: document.querySelector('meta[name="csrf-token"]').content,
    };

    if (reactionMode === 'add' && !this.store.isAdmin) {
      const reactionCount = this.store.reactions[targetId]?.length ?? 0;
      if (reactionCount >= this.store.options.reactionLimit) {
        if (!this.store.reactions[targetId].find(item => item.reaction === reaction)?.choose) {
          alert('리액션은 최대 ' + this.store.options.reactionLimit + '개까지 가능합니다.');
          return;
        }
      }
    }

    const response = await fetch(this.endpoints.react, {
      method: 'POST',
      cache: "no-cache",
      headers: {
        'Accept': 'application/json',
        'Content-type': 'application/json',
      },
      body: JSON.stringify(requestBody),
    });

    const jsonData = await response.json();

    if (jsonData.status === 'success' || jsonData.error === 0) {
      this.store.reactions[targetId] = jsonData.reactions;
    } else {
      alert(jsonData.message);
    }
  }
}

window.daReaction = new DaReaction();
