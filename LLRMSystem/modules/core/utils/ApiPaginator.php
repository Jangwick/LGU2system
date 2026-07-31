<?php

/**
 * API Paginator
 *
 * Standardizes pagination for list responses with a consistent JSON envelope.
 * Supports both in-memory (fallback) and database-level pagination.
 */

class ApiPaginator {
    public const DEFAULT_PER_PAGE = 20;
    public const MAX_PER_PAGE = 100;

    public static function resolveParams(): array {
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : self::DEFAULT_PER_PAGE;

        $page = max(1, $page);
        $perPage = min(max(1, $perPage), self::MAX_PER_PAGE);

        return [$page, $perPage];
    }

    public static function envelope(array $data, int $page, int $perPage, int $total, bool $success = true, string $message = ''): array {
        $totalPages = (int) ceil($total / $perPage);

        return [
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
            ]
        ];
    }

    public static function paginateArray(array $items, int $page = 1, int $perPage = self::DEFAULT_PER_PAGE): array {
        [$page, $perPage] = self::normalize($page, $perPage);
        $total = count($items);
        $offset = ($page - 1) * $perPage;
        $data = array_slice($items, $offset, $perPage);
        return self::envelope($data, $page, $perPage, $total);
    }

    public static function paginateQuery(PDO $db, string $baseSql, ?string $countSql, array $params = [], int $page = 1, int $perPage = self::DEFAULT_PER_PAGE): array {
        [$page, $perPage] = self::normalize($page, $perPage);
        $offset = ($page - 1) * $perPage;

        $stmt = $db->prepare($baseSql . " LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total = 0;
        if ($countSql !== null) {
            $countStmt = $db->query($countSql);
            $total = (int) $countStmt->fetchColumn();
        } else {
            $total = count($data);
        }

        return self::envelope($data, $page, $perPage, $total);
    }

    private static function normalize(int $page, int $perPage): array {
        $page = max(1, $page);
        $perPage = min(max(1, $perPage), self::MAX_PER_PAGE);
        return [$page, $perPage];
    }
}
