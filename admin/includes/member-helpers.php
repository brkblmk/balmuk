<?php
if (!function_exists('ensureMemberManagementTables')) {
    function ensureMemberManagementTables(PDO $pdo): void
    {
        $tableSql = [
            "CREATE TABLE IF NOT EXISTS member_payments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                member_id INT NOT NULL,
                package_id INT NULL,
                amount DECIMAL(12,2) NOT NULL DEFAULT 0,
                currency VARCHAR(5) NOT NULL DEFAULT 'TRY',
                sessions_purchased INT NOT NULL DEFAULT 0,
                payment_date DATE NOT NULL,
                payment_method VARCHAR(50) NULL,
                reference_code VARCHAR(100) NULL,
                notes TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                INDEX idx_member (member_id),
                INDEX idx_payment_date (payment_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS member_sessions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                member_id INT NOT NULL,
                session_date DATE NOT NULL,
                session_type VARCHAR(100) NULL,
                duration_minutes SMALLINT NULL,
                intensity_level VARCHAR(50) NULL,
                trainer_name VARCHAR(100) NULL,
                notes TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_member (member_id),
                INDEX idx_session_date (session_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS member_metrics (
                id INT AUTO_INCREMENT PRIMARY KEY,
                member_id INT NOT NULL,
                measurement_date DATE NOT NULL,
                weight_kg DECIMAL(6,2) NULL,
                body_fat_percentage DECIMAL(5,2) NULL,
                muscle_mass_kg DECIMAL(6,2) NULL,
                visceral_fat DECIMAL(5,2) NULL,
                water_percentage DECIMAL(5,2) NULL,
                waist_cm DECIMAL(5,2) NULL,
                hip_cm DECIMAL(5,2) NULL,
                notes TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_member (member_id),
                INDEX idx_measurement_date (measurement_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        ];

        foreach ($tableSql as $sql) {
            try {
                $pdo->exec($sql);
            } catch (PDOException $e) {
                error_log('Member table sync error: ' . $e->getMessage());
            }
        }
    }
}

if (!function_exists('getMemberAggregates')) {
    function getMemberAggregates(PDO $pdo, int $memberId): array
    {
        $stats = [
            'sessions_completed' => 0,
            'sessions_purchased' => 0,
            'total_amount' => 0.0,
            'last_payment' => null
        ];

        try {
            $sessionStmt = $pdo->prepare('SELECT COUNT(*) FROM member_sessions WHERE member_id = ?');
            $sessionStmt->execute([$memberId]);
            $stats['sessions_completed'] = (int)$sessionStmt->fetchColumn();
        } catch (PDOException $e) {
            $stats['sessions_completed'] = 0;
        }

        try {
            $purchaseStmt = $pdo->prepare(
                'SELECT COALESCE(SUM(sessions_purchased),0) as sessions, COALESCE(SUM(amount),0) as total, MAX(payment_date) as last_payment
                 FROM member_payments WHERE member_id = ?'
            );
            $purchaseStmt->execute([$memberId]);
            $row = $purchaseStmt->fetch();

            if ($row) {
                $stats['sessions_purchased'] = (int)($row['sessions'] ?? 0);
                $stats['total_amount'] = (float)($row['total'] ?? 0);
                $stats['last_payment'] = $row['last_payment'] ?? null;
            }
        } catch (PDOException $e) {
            // leave defaults
        }

        return $stats;
    }
}

if (!function_exists('getPaymentAnalytics')) {
    function getPaymentAnalytics(PDO $pdo): array
    {
        $analytics = [
            'year_total' => 0.0,
            'last_month_total' => 0.0,
            'last_30_total' => 0.0,
            'monthly_labels' => [],
            'monthly_totals' => [],
            'daily_labels' => [],
            'daily_totals' => [],
            'method_breakdown' => []
        ];

        $queries = [
            'year_total' => "SELECT COALESCE(SUM(amount),0) FROM member_payments WHERE YEAR(payment_date) = YEAR(CURDATE())",
            'last_month_total' => "SELECT COALESCE(SUM(amount),0) FROM member_payments WHERE YEAR(payment_date) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND MONTH(payment_date) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))",
            'last_30_total' => "SELECT COALESCE(SUM(amount),0) FROM member_payments WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"
        ];

        foreach ($queries as $key => $sql) {
            try {
                $analytics[$key] = (float)$pdo->query($sql)->fetchColumn();
            } catch (PDOException $e) {
                $analytics[$key] = 0.0;
            }
        }

        try {
            $monthlyStmt = $pdo->query(
                "SELECT DATE_FORMAT(payment_date, '%Y-%m') as label, COALESCE(SUM(amount),0) as total
                 FROM member_payments
                 WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
                 GROUP BY label
                 ORDER BY label ASC"
            );
            foreach ($monthlyStmt->fetchAll() as $row) {
                $analytics['monthly_labels'][] = $row['label'];
                $analytics['monthly_totals'][] = (float)$row['total'];
            }
        } catch (PDOException $e) {
            $analytics['monthly_labels'] = [];
            $analytics['monthly_totals'] = [];
        }

        try {
            $dailyStmt = $pdo->query(
                "SELECT DATE(payment_date) as label, COALESCE(SUM(amount),0) as total
                 FROM member_payments
                 WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                 GROUP BY label
                 ORDER BY label ASC"
            );
            foreach ($dailyStmt->fetchAll() as $row) {
                $analytics['daily_labels'][] = $row['label'];
                $analytics['daily_totals'][] = (float)$row['total'];
            }
        } catch (PDOException $e) {
            $analytics['daily_labels'] = [];
            $analytics['daily_totals'] = [];
        }

        try {
            $methodStmt = $pdo->query(
                "SELECT payment_method, COALESCE(SUM(amount),0) as total
                 FROM member_payments
                 WHERE payment_method IS NOT NULL AND payment_method != ''
                 GROUP BY payment_method
                 ORDER BY total DESC"
            );
            $analytics['method_breakdown'] = $methodStmt->fetchAll();
        } catch (PDOException $e) {
            $analytics['method_breakdown'] = [];
        }

        return $analytics;
    }
}
