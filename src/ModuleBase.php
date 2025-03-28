<?php
declare(strict_types=1);

namespace Rhymix\Modules\Da_reaction\Src;

use Rhymix\Modules\Da_reaction\Src\Models\ReactionConfig;
use Rhymix\Modules\Da_reaction\Src\Models\ReactionPartConfig;

/**
 * 라이믹스 리액션 모듈
 *
 * @copyright 2025 kkigomi
 * @license gnu-gpl-v2-or-later
 * @link https://github.com/damoang-users/rx-da_reaction
 */
class ModuleBase extends \ModuleObject
{
    /** 리액션 할 수 없음 */
    public const NOT_REACTABLE = 0;
    /** 리액션 추가 가능 */
    public const REACTABLE_ADD = 1;
    /** 리액션 취소 가능 */
    public const REACTABLE_REVOKE = 2;
    /** 리액션 추가 및 취소 가능 */
    public const REACTABLE = 3;

    /** reaction 데이터 테이블 */
    public static string $tableReaction = 'da_reaction';
    /** 회원의 리액션 이력 테이블 */
    public static string $tableReactionChoose = 'da_reaction_choose';

    /** @var ReactionConfig */
    protected static ReactionConfig $config;

    /** @var array<int,ReactionPartConfig> */
    protected static array $partConfigInstances = [];

    /**
     * @return array<string,mixed>
     */
    public static function loadCustomConfig(): array
    {
        $customData = [];

        try {
            if (file_exists(__DIR__ . '/../config.php')) {
                $customData = include __DIR__ . '/../config.php';
            }
        } catch (\Throwable $e) {
            return [];
        }

        // FIXME
        if (!is_array($customData)) {
            return [];
        }

        return $customData;
    }

    /**
     * 모듈의 설정 반환
     */
    public static function getConfig(): ReactionConfig
    {
        if (!isset(static::$config)) {
            static::$config = new ReactionConfig();
        }

        return static::$config;
    }

    /**
     * 게시판별 리액션 모듈 설정 반환
     */
    public static function getPartConfig(int $moduleSrl): ReactionPartConfig
    {
        if (!isset(static::$partConfigInstances[$moduleSrl])) {
            static::$partConfigInstances[$moduleSrl] = new ReactionPartConfig(static::getConfig(), $moduleSrl);
        }

        return static::$partConfigInstances[$moduleSrl];
    }
}
