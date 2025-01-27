<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Query;

use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use ChronicleKeeper\Shared\Infrastructure\Database\DatabasePlatform;

use function assert;
use function trim;

class FindAllReferencableMediaQuery implements Query
{
    public function __construct(private readonly DatabasePlatform $databasePlatform)
    {
    }

    /** @return array<array<string, string>> */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof FindAllReferencableMedia);

        $foundMedia = $parameters->search === ''
            ? $this->getFindAllQuery()
            : $this->getFindAllBySearchTermQuery($parameters->search);

        $formattedMedia = [];
        foreach ($foundMedia as $media) {
            $formattedMedia[] = [
                'value' => $media['type'] . '_' . $media['id'],
                'text' => trim($media['title']),
            ];
        }

        return $formattedMedia;
    }

    /** @return array<array<string, string>> */
    private function getFindAllQuery(): array
    {
        $query = <<<'SQL'
            SELECT 'document' as type, id, title FROM documents
            UNION
            SELECT 'image' as type, id, title FROM images
            UNION
            SELECT 'conversation' as type, id, title FROM conversations
            ORDER BY title
        SQL;

        return $this->databasePlatform->fetch($query);
    }

    /** @return array<array<string, string>> */
    private function getFindAllBySearchTermQuery(string $search): array
    {
        $query = <<<'SQL'
            SELECT 'document' as type, id, title FROM documents WHERE title LIKE :search
            UNION
            SELECT 'image' as type, id, title FROM images WHERE title LIKE :search
            UNION
            SELECT 'conversation' as type, id, title FROM conversations WHERE title LIKE :search
            ORDER BY title
        SQL;

        return $this->databasePlatform->fetch($query, ['search' => '%' . $search . '%']);
    }
}
