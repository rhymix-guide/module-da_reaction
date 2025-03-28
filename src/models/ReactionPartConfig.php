<?php
declare(strict_types=1);

namespace Rhymix\Modules\Da_reaction\Src\Models;

use ModuleController;
use ModuleModel;

/**
 * 리액션 모듈의 개별 인스턴스 설정
 *
 * @inheritDoc
 * @property bool $ignore_part_config
 */
class ReactionPartConfig extends ReactionConfig
{
    private int $moduleSrl;

    private ReactionConfig $moduleConfig;

    public function __construct(ReactionConfig $moduleConfig, int $moduleSrl)
    {
        $this->moduleSrl = $moduleSrl;
        $this->moduleConfig = $moduleConfig;

        $config = ModuleModel::getModulePartConfig($this->configKey, $this->moduleSrl) ?? new \stdClass();
        if (!is_object($config)) {
            $config = new \stdClass();
        }

        /** @var \stdClass $config */
        $this->config = $this->moduleConfig->gets();
        $this->config->ignore_part_config = $config->ignore_part_config ?? false;

        if (!$this->config->ignore_part_config) {
            if (!$this->config->enable) {
                $config->enable = false;
            }

            // @phpstan-ignore assign.propertyType
            $this->config = (object) array_merge((array) $this->config, (array) $config);
        }
    }

    public function moduelSrl(): int
    {
        return $this->moduleSrl;
    }

    /**
     * 설정 변경사항 저장
     */
    public function save(): \BaseObject
    {
        // @phpstan-ignore assign.propertyType
        $this->config = (object) $this->sanitize((array) $this->config);

        $oModuleController = ModuleController::getInstance();
        $output = $oModuleController->insertModulePartConfig($this->configKey, $this->moduleSrl, $this->gets());

        return $output;
    }
}
