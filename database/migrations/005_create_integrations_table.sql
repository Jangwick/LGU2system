-- Integration Module Tables
CREATE TABLE IF NOT EXISTS integrated_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_type VARCHAR(50) NOT NULL, -- 'ordinance', 'session', 'agenda', etc.
    external_id VARCHAR(100),
    title VARCHAR(255) NOT NULL,
    summary TEXT,
    data_payload JSON,
    status ENUM('pending', 'processed', 'synced', 'failed') DEFAULT 'pending',
    source_system VARCHAR(100) DEFAULT 'External API',
    received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL
);

-- Seed some sample data for demonstration
INSERT INTO integrated_records (module_type, title, summary, data_payload, status, source_system) VALUES 
('ordinance', 'City Ordinance No. 2024-001', 'An ordinance prohibiting single-use plastics.', '{"enacted_date": "2024-01-15", "proponent": "Hon. John Doe"}', 'synced', 'E-Legislative System'),
('session', 'Regular Session #42', 'Weekly budget deliberation session.', '{"date": "2024-02-01", "attendees": 12}', 'processed', 'Session Manager Pro'),
('agenda', 'Environment Committee Meeting', 'Discussion on waste management.', '{"priority": "high"}', 'pending', 'Agenda Planner');
