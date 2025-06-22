<?php

declare(strict_types=1);

namespace ChronicleKeeper\World\Application\Query;

use ChronicleKeeper\Shared\Application\Query\Query;
use ChronicleKeeper\Shared\Application\Query\QueryParameters;
use Doctrine\DBAL\Connection;

use function assert;
use function strtolower;
use function trim;

class FindAllReferencableMediaQuery implements Query
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /** @return array<array<string, string>> */
    public function query(QueryParameters $parameters): array
    {
        assert($parameters instanceof FindAllReferencableMedia);

        $foundMedia = $parameters->search === ''
            ? $this->findAllMedia()
            : $this->findMediaBySearchTerm($parameters->search);

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
    private function findAllMedia(): array
    {
        $sql = <<<'SQL'
            SELECT 'document' as type, id, title FROM documents
            UNION
            SELECT 'image' as type, id, title FROM images
            UNION
            SELECT 'conversation' as type, id, title FROM conversations
            ORDER BY title
        SQL;

        return $this->connection->fetchAllAssociative($sql);
    }

    /** @return array<array<string, string>> */
    private function findMediaBySearchTerm(string $search): array
    {
        $sql = <<<'SQL'
            SELECT 'document' as type, id, title FROM documents WHERE LOWER(title) LIKE :search
            UNION
            SELECT 'image' as type, id, title FROM images WHERE LOWER(title) LIKE :search
            UNION
            SELECT 'conversation' as type, id, title FROM conversations WHERE LOWER(title) LIKE :search
            ORDER BY title
        SQL;

        return $this->connection->fetchAllAssociative(
            $sql,
            ['search' => '%' . strtolower($search) . '%'],
        );
    }
}
