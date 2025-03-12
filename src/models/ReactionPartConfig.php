<?php
declare(strict_types=1);

namespace Rhymix\Modules\Da_reaction\Src\Models;

use ModuleController;
use ModuleModel;
use Rhymix\Framework\Exception;

/**
 * 리액션 모듈의 개별 인스턴스 설정
 *
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

        $config = ModuleModel::getModulePartConfig($this->configKey, $this->moduleSrl);
        if ($config === null || !is_object($config)) {
            $config = new \stdClass();
        }

        $this->config = $this->moduleConfig->gets();
        $this->config->ignore_part_config = $config->ignore_part_config;

        if (!$this->config->ignore_part_config) {
            if (!$this->config->enable) {
                $config->enable = false;
            }

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
        $this->config = (object) $this->sanitize((array) $this->config);

        $oModuleController = ModuleController::getInstance();
        $output = $oModuleController->insertModulePartConfig($this->configKey, $this->moduleSrl, $this->gets());

        return $output;
    }
}
