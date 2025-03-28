# Changelog

## [1.2.0](https://github.com/damoang-users/rx-da_reaction/compare/v1.1.1...v1.2.0) (2025-03-28)

### Features

* [#21](https://github.com/damoang-users/rx-da_reaction/issues/21) 글을 삭제할 때 리액션 데이터 삭제 ([f77037d](https://github.com/damoang-users/rx-da_reaction/commit/f77037d1b425f8cceec07eec13a4979be3a4b428))
* [#21](https://github.com/damoang-users/rx-da_reaction/issues/21) 글을 이동할 때 리액션 데이터 처리 ([f67635a](https://github.com/damoang-users/rx-da_reaction/commit/f67635ae3a81c6209cc070c0eee259f77e8b59e5))
* [#21](https://github.com/damoang-users/rx-da_reaction/issues/21) 댓글을 삭제할 때 리액션 데이터 삭제 ([83daaba](https://github.com/damoang-users/rx-da_reaction/commit/83daaba7e90f14f95fdbfd8c0c9ca576230356fd))
* Fetch 응답에서 라이믹스 디버그 패널 및 PHPDebugbar 지원 ([459b9e3](https://github.com/damoang-users/rx-da_reaction/commit/459b9e3d7df4a517ab2f57587de1f955ef3925ad))

### Bug Fixes

* [#21](https://github.com/damoang-users/rx-da_reaction/issues/21) 댓글 삭제 시 리스너의 객체 타입 문제 고침 ([2ef64be](https://github.com/damoang-users/rx-da_reaction/commit/2ef64bea07b71836f66dcbfda24a7380ee454d0d))
* [#32](https://github.com/damoang-users/rx-da_reaction/issues/32) countable 문제 수정 ([c761462](https://github.com/damoang-users/rx-da_reaction/commit/c761462cd02ff2d8ed7c25937c9cea48f782a258))
* 댓글의 targetId를 생성할 때 문서 번호가 없을 때 잘못된 포맷으로 생성되는 문제 고침 ([2cb67e7](https://github.com/damoang-users/rx-da_reaction/commit/2cb67e7ac10133dc5511534a1281ee029e134b49))
* 리액션 횟수 제한이 잘못 적용되는 문제 수정 ([60151d6](https://github.com/damoang-users/rx-da_reaction/commit/60151d6ea280fe68951054e09e808ad6b0a660f6))

## [1.1.1](https://github.com/damoang-users/rx-da_reaction/compare/v1.1.0...v1.1.1) (2025-03-16)

### Features

* 리액션 버튼 hover 및 리액션 시 배경색 추가 by @rzglitch in https://github.com/damoang-users/rx-da_reaction/pull/24

### Bug Fixes

* 디버깅용 코드 제거 ([566822b](https://github.com/damoang-users/rx-da_reaction/commit/566822baca7e34d917ecdd9582001281e176cf0c))

### New Contributors
* [@rzglitch](https://github.com/rzglitch) made their first contribution in https://github.com/damoang-users/rx-da_reaction/pull/24

## [1.1.0](https://github.com/damoang-users/rx-da_reaction/compare/v1.0.3...v1.1.0) (2025-03-15)

### Features

* 모듈 및 게시판 설정 추가 및 설정 적용 ([34fc89a](https://github.com/damoang-users/rx-da_reaction/commit/34fc89a6e8204ef2120d9e42a41a5be0faaa46e4)), closes [#7](https://github.com/damoang-users/rx-da_reaction/issues/7) [#15](https://github.com/damoang-users/rx-da_reaction/issues/15)

### Bug Fixes

* [#12](https://github.com/damoang-users/rx-da_reaction/issues/12) 사용하지않는 metadata 필드 제거 ([70bce37](https://github.com/damoang-users/rx-da_reaction/commit/70bce370667b30616b0a74ab874c16b631ff933e))
* **config:** [#7](https://github.com/damoang-users/rx-da_reaction/issues/7) 관리자의 리액션 타입을 잘못 처리한 문제 고침 ([fb1cb5a](https://github.com/damoang-users/rx-da_reaction/commit/fb1cb5ac8aad3749efd9bcc1ac7c24d26383e969))
* **config:** [#7](https://github.com/damoang-users/rx-da_reaction/issues/7) 그룹 제한 시 리액션을 취소할 수 없는 문제 고침 ([f096e7a](https://github.com/damoang-users/rx-da_reaction/commit/f096e7a417db9d3c78b81676aed411f54dae19d2))
* **config:** [#7](https://github.com/damoang-users/rx-da_reaction/issues/7) 그룹이 설정되지 않았을 때 오류 수정 ([3e52e58](https://github.com/damoang-users/rx-da_reaction/commit/3e52e5845fba141e2bce4f9a088187ccf42906f6))
* **config:** [#7](https://github.com/damoang-users/rx-da_reaction/issues/7) 리액션 토글 시 갯수 제한이 적용되는 문제 고침 ([995314a](https://github.com/damoang-users/rx-da_reaction/commit/995314aeaf1a350d7756a6916aa67eaf6a1ea394))
* **config:** [#7](https://github.com/damoang-users/rx-da_reaction/issues/7) 리액션 횟수 제한을 백엔드에서 체크하지 못하는 문제 고침 ([230fc07](https://github.com/damoang-users/rx-da_reaction/commit/230fc07dfbd638e24988f9d585906efe6c39db33))

# [v1.0.3](https://github.com/damoang-users/rx-da_reaction/compare/v1.0.2...v1.0.3) (2025-03-12)

### Bug Fixes

* 디버깅을 위해 사용한 코드 제거 ([4989602](https://github.com/damoang-users/rx-da_reaction/commit/498960271f0fff035c4005033d5caedec6a45126))


# [v1.0.2](https://github.com/damoang-users/rx-da_reaction/compare/v1.0.1...v1.0.2) (2025-03-11)

### Bug Fixes

* [#3](https://github.com/damoang-users/rx-da_reaction/issues/3) 리액션 popover를 표시할 때 포커스가 잘못 이동하는 문제 수정 ([a51f9dc](https://github.com/damoang-users/rx-da_reaction/commit/a51f9dc7bf938726cb0c357ac952aee905b74574))


# [v1.0.1](https://github.com/damoang-users/rx-da_reaction/compare/v1.0.0...v1.0.1) (2025-03-11)

### Bug Fixes

* [#3](https://github.com/damoang-users/rx-da_reaction/issues/3) CSRF 토큰 사용 설정 시 문제 수정 ([fba5cea](https://github.com/damoang-users/rx-da_reaction/commit/fba5ceae394490a6ee72ac4d9b8af5f29a913def))

# v1.0.0 (2025-03-11)

🎉 라이믹스 리액션 모둘의 첫번째 릴리즈입니다.
