<?php
declare(strict_types=1);

namespace Rhymix\Modules\Da_reaction\Src\Models;

use ModuleController;
use ModuleModel;
use Rhymix\Framework\Exceptions\MustLogin;
use Rhymix\Framework\Exceptions\NotPermitted;
use Rhymix\Framework\Helpers\SessionHelper;

/**
 * 리액션 모듈의 설정
 *
 * @property bool $enable
 * @property int $reaction_limit 리액션 제한 수
 * @property bool $reaction_self 자신의 글, 댓글에 리액션 제한
 * @property string $reaction_allows
 * @property string $document_insert_position 문서 리액션 버튼 추가 위치
 * @property string $comment_insert_position 댓글 리액션 버튼 추가 위치
 */
class ReactionConfig
{
    protected string $configKey = 'da_reaction';

    /**
     * @var object
     */
    protected object $config;

    /**
     * @var array{
     *     enable: bool,
     *     ignore_part_config: bool,
     *     reaction_limit: int,
     *     reaction_self: bool,
     *     reaction_allows: string,
     *     document_insert_position: string,
     *     comment_insert_position: string,
     * }
     */
    protected array $defaultConfig = [
        'enable' => true,
        'ignore_part_config' => false,
        'reaction_limit' => 20,
        'reaction_self' => true,
        'reaction_allows' => '',
        'document_insert_position' => 'after',
        'comment_insert_position' => 'after',
    ];

    /**
     * @var array<string,string|mixed[]>
     */
    protected array $filter = [
        'enable' => 'bool',
        'ignore_part_config' => 'bool',
        'reaction_limit' => 'int',
        'reaction_self' => 'bool',
        'reaction_allows' => 'string',
        'document_insert_position' => ['before', 'after', 'disable'],
        'comment_insert_position' => ['before', 'after', 'disable'],
    ];

    public function __construct()
    {
        $moduleConfig = ModuleModel::getModuleConfig($this->configKey);
        if ($moduleConfig === null || !is_object($moduleConfig)) {
            $moduleConfig = new \stdClass();
        }

        $this->config = (object) array_merge($this->defaultConfig, (array) $moduleConfig);
    }

    /**
     * 모듈 활성화 여부
     */
    public function isEnable(): bool
    {
        return $this->config->enable;
    }

    /**
     * @throws MustLogin
     * @throws NotPermitted
     */
    public function reactable(SessionHelper $member): bool
    {
        if (!$member->isMember()) {
            throw new MustLogin();
        }

        if ($this->getAllowGroups()) {
            $groups = array_keys($member->getGroups());
            if (!count(array_intersect($groups, $this->getAllowGroups()))) {
                throw new NotPermitted();
            }
        }

        return true;
    }

    /**
     * @return int[]
     */
    public function getAllowGroups(): array
    {
        $groups = explode(',', $this->config->reaction_allows);
        $groups = array_map('intval', $groups);

        return $groups;
    }

    /**
     * @param mixed[] $vars
     */
    public function setVars(array $vars): void
    {
        $vars['reaction_allows'] = implode(',', $vars['reaction_allows'] ?? []);
        $vars = array_merge((array) $this->config, $vars);

        $this->config = (object) $this->sanitize($vars);
    }

    /**
     * 설정 변경사항 저장
     */
    public function save(): \BaseObject
    {
        $this->config = (object) $this->sanitize(array_merge($this->defaultConfig, (array) $this->config));

        $oModuleController = ModuleController::getInstance();
        $output = $oModuleController->insertModuleConfig($this->configKey, $this->config);

        return $output;
    }

    /**
     * @param mixed[] $config
     * @return mixed[]
     */
    protected function sanitize(array $config, bool $cleanup = false): array
    {
        return array_reduce(array_keys($config), function ($carry, $key) use ($config, $cleanup) {
            if ($config[$key] === null) {
                return $carry;
            }

            if (!array_key_exists($key, $this->filter)) {
                return $carry;
            }

            $value = $config[$key];

            if ($this->filter[$key] === 'string') {
                $value = strval($value);
            } else if ($this->filter[$key] === 'int') {
                $value = intval($value);
            } else if ($this->filter[$key] === 'float') {
                $value = floatval($value);
            } else if ($this->filter[$key] === 'array') {
                $value = (array) $value;
            } else if ($this->filter[$key] === 'bool') {
                if (!is_bool($value)) {
                    $value = in_array(
                        strtolower(strval($value)),
                        ['yes', 'y', 'true', 't', 'on', 'ok', 'enable', 'enabled', 'checked', 'selected', '1'],
                        true
                    );
                }
            } else if (is_array($this->filter[$key])) {
                if (!in_array($value, $this->filter[$key], true)) {
                    $value = $this->defaultConfig[$key];
                }
            }

            if ($cleanup && $value === $this->defaultConfig[$key]) {
                return $carry;
            }

            $carry[$key] = $value;

            return $carry;
        }, []);
    }

    public function gets(): object
    {
        return $this->config;
    }

    public function __serialize(): array
    {
        return (array) $this->config;
    }

    /**
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->config->{$name} ?? null;
    }

    /**
     * @param ?mixed $value
     */
    public function __set(string $name, $value): void
    {
        $this->config->{$name} = $this->sanitize([$name => $value])[$name] ?? $this->defaultConfig[$name];
    }
}
