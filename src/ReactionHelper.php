<?php
declare(strict_types=1);

namespace Rhymix\Modules\Da_reaction\Src;

use FileHandler;
use ModuleHandler;
use Rhymix\Framework\URL;

/**
 * @template TReactionItem of array{
 *     reaction: string,
 *     type: string,
 *     id: string,
 *     count: int,
 *     choose: bool,
 * }
 */
class ReactionHelper
{
    public static function generateIdByDocument(int $moduleSrl, int $documentSrl): ?string
    {
        return implode(':', ['document', $moduleSrl, $documentSrl]);
    }

    /**
     * @param \DocumentItem|\CommentItem|\BaseObject $object
     */
    public static function generateIdByItem(object $object): ?string
    {
        if ($object instanceof \DocumentItem) {
            return self::generateIdByDocument($object->get('module_srl'), $object->get('document_srl'));
        } else if ($object instanceof \CommentItem) {
            return self::generateIdByComment($object->get('module_srl'), $object->get('comment_srl'), $object->get('document_srl'));
        }

        return null;
    }

    public static function generateIdByComment(int $moduleSrl, int $commentSrl, ?int $documentSrl): ?string
    {
        $targetId = implode(':', ['comment', $moduleSrl, $commentSrl]);
        if ($documentSrl) {
            $parentId = self::generateIdByDocument($moduleSrl, $documentSrl);
        }

        return $documentSrl ? "{$targetId}@{$parentId}" : $targetId;
    }

    /**
     * @throws Exceptions\ReactionIdTooLongException
     */
    public static function validateReactionId(string $reactionId): bool
    {
        if (strlen($reactionId) > 250) {
            throw new Exceptions\ReactionIdTooLongException();
        }

        return true;
    }

    /**
     * @throws Exceptions\TargetIdTooLongException
     */
    public static function validateTargetId(string $targetId): bool
    {
        if (strlen($targetId) > 250) {
            throw new Exceptions\TargetIdTooLongException();
        }

        return true;
    }

    /**
     * @return array{
     *     reaction: string,
     *     url: string,
     * }
     */
    public static function getImportImages(): array
    {
        $modulePath = ModuleHandler::getModulePath('da_reaction');

        $files = glob(FileHandler::getRealPath("{$modulePath}public/emoticon-images/*")) ?: [];

        $imageList = array_reduce(
            $files,
            function ($carry, $file) {
                $fileinfo = pathinfo($file);
                $validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
                if (in_array(strtolower($fileinfo['extension'] ?? ''), $validExtensions)) {
                    $filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $fileinfo['filename']);
                    $filename = strtolower($filename);
                    $carry[] = [
                        'reaction' => "import-image:{$filename}",
                        'url' => URL::fromServerPath($file),
                    ];
                }
                return $carry;
            },
            []
        );

        return $imageList;
    }

    /**
     * @return TReactionItem
     */
    public static function parseReaction(string $reaction, int $count = 0): array
    {
        $parts = explode(':', $reaction, 2);

        /** @var TReactionItem $result */
        $result = [
            'reaction' => $reaction,
            'type' => $parts[0] ?: '',
            'id' => $parts[1] ?: '',
            'count' => $count,
            'choose' => false,
        ];

        return $result;
    }

    /**
     * @return mixed[]
     */
    public static function parseTargetId(string $target): array
    {
        $result = [];

        $parts = explode(':', $target);
        if ($parts[0] === 'document' && count($parts) === 3) {
            $result['type'] = $parts[0];
            $result['module_srl'] = intval($parts[1]);
            $result['document_srl'] = intval($parts[2]);
        } else if ($parts[0] === 'comment' && count($parts) === 3) {
            $result['type'] = $parts[0];
            $result['module_srl'] = intval($parts[1]);
            $result['comment_srl'] = intval($parts[2]);
        } else {
            $result = $parts;
        }

        return $result;
    }
}
