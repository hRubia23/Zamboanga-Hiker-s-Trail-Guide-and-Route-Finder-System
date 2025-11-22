<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once '../includes/auth.php';
require_once '../includes/db.php';

// Get all reviews grouped by status
try {
    $stmt = $pdo->prepare("
        SELECT 
            r.id,
            r.rating,
            r.comment,
            r.created_at,
            r.status,
            u.username,
            t.name as trail_name 
        FROM reviews r 
        JOIN trails t ON r.trail_id = t.id 
        JOIN users u ON r.user_id = u.id
        ORDER BY r.created_at DESC
    ");
    $stmt->execute();
    $all_reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group reviews by status
    $pending_reviews = array_filter($all_reviews, fn($r) => $r['status'] === 'pending');
    $approved_reviews = array_filter($all_reviews, fn($r) => $r['status'] === 'approved');
    $rejected_reviews = array_filter($all_reviews, fn($r) => $r['status'] === 'rejected');
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle status messages
$message = '';
$message_type = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'approved') {
        $message = 'Review approved successfully!';
        $message_type = 'success';
    } elseif ($_GET['success'] === 'rejected') {
        $message = 'Review rejected successfully!';
        $message_type = 'success';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Management - Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', sans-serif;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 50%, #bbf7d0 100%);
    min-height: 100vh;
    padding: 40px 20px;
}

.container {
    max-width: 1400px;
    margin: 0 auto;
}

.back-link {
    display: inline-block;
    margin-bottom: 20px;
    color: #16a34a;
    text-decoration: none;
    font-weight: 600;
    padding: 10px 20px;
    background: white;
    border-radius: 12px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.back-link:hover {
    background: #dcfce7;
    transform: translateX(-5px);
}

h1 {
    font-size: 3rem;
    color: #14532d;
    margin-bottom: 40px;
    font-weight: 900;
    text-align: center;
}

/* Tabs */
.tabs {
    display: flex;
    gap: 15px;
    margin-bottom: 30px;
    justify-content: center;
}

.tab-button {
    padding: 14px 32px;
    border: none;
    border-radius: 50px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.3s;
    background: white;
    color: #64748b;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.tab-button.active {
    background: linear-gradient(135deg, #16a34a, #22c55e);
    color: white;
    transform: translateY(-3px);
}

/* Table */
.review-table-container {
    background: white;
    padding: 20px;
    border-radius: 16px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    overflow-x: auto;
}

.review-table {
    width: 100%;
    border-collapse: collapse;
}

.review-table th {
    text-align: left;
    padding: 14px 16px;
    background: #f0fdf4;
    color: #14532d;
    font-weight: 700;
    border-bottom: 2px solid #bbf7d0;
}

.review-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #e5e7eb;
}

.status-badge-table {
    padding: 6px 12px;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 700;
}

.status-approved { background: #dcfce7; color: #15803d; }
.status-rejected { background: #fee2e2; color: #dc2626; }
.status-pending { background: #fef3c7; color: #92400e; }

/* Modal */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 999;
}

.modal-box {
    background: white;
    width: 500px;
    padding: 25px;
    border-radius: 18px;
}

.modal-close {
    float: right;
    cursor: pointer;
    font-weight: bold;
    font-size: 1.2rem;
}
</style>

<body>
<div class="container">

    <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
    <h1>📋 Review Management</h1>

    <?php if ($message): ?>
        <div class="message message-<?= $message_type ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Stats -->


    <!-- Tabs -->
    <div class="tabs">
        <button class="tab-button" onclick="showTab('approved')">✅ Approved (<?= count($approved_reviews) ?>)</button>
        <button class="tab-button" onclick="showTab('rejected')">❌ Rejected (<?= count($rejected_reviews) ?>)</button>
        <button class="tab-button" onclick="showTab('all')">📊 All (<?= count($all_reviews) ?>)</button>
    </div>

    <!-- Approved Tab -->
    <div id="approved-tab" class="tab-content">
        <div class="review-table-container">
            <?php if (count($approved_reviews) > 0): ?>
            <table class="review-table">
                <thead>
                    <tr>
                        <th>Trail</th>
                        <th>User</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Comment</th>
                        
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($approved_reviews as $review): ?>
                    <tr>
                        <td><?= htmlspecialchars($review['trail_name']) ?></td>
                        <td><?= htmlspecialchars($review['username']) ?></td>
                        <td><?= str_repeat("⭐", $review['rating']) ?></td>
                        <td><span class="status-badge-table status-approved">Approved</span></td>
                        <td><?= date('M d, Y - h:i A', strtotime($review['created_at'])) ?></td>

                        <td>
                            <button onclick='openCommentModal(`<?= addslashes($review["comment"]) ?>`)' 
                                    class="btn btn-approve" 
                                    style="padding:8px 16px; border-radius:10px;">
                                View
                            </button>
                        </td>

                        
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="no-reviews"><p>No approved reviews.</p></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Rejected Tab -->
    <div id="rejected-tab" class="tab-content">
        <div class="review-table-container">
            <?php if (count($rejected_reviews) > 0): ?>
            <table class="review-table">
                <thead>
                    <tr>
                        <th>Trail</th>
                        <th>User</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Comment</th>
                        
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rejected_reviews as $review): ?>
                    <tr>
                        <td><?= htmlspecialchars($review['trail_name']) ?></td>
                        <td><?= htmlspecialchars($review['username']) ?></td>
                        <td><?= str_repeat("⭐", $review['rating']) ?></td>
                        <td><span class="status-badge-table status-rejected">Rejected</span></td>
                        <td><?= date('M d, Y - h:i A', strtotime($review['created_at'])) ?></td>

                        <td>
                            <button onclick='openCommentModal(`<?= addslashes($review["comment"]) ?>`)' 
                                class="btn btn-approve" 
                                style="padding:8px 16px; border-radius:10px;">
                                View
                            </button>
                        </td>

                        
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="no-reviews"><p>No rejected reviews.</p></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- All Tab -->
    <div id="all-tab" class="tab-content">
        <div class="review-table-container">
            <?php if (count($all_reviews) > 0): ?>
            <table class="review-table">
                <thead>
                    <tr>
                        <th>Trail</th>
                        <th>User</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Comment</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($all_reviews as $review): ?>
                    <tr>
                        <td><?= htmlspecialchars($review['trail_name']) ?></td>
                        <td><?= htmlspecialchars($review['username']) ?></td>
                        <td><?= str_repeat("⭐", $review['rating']) ?></td>
                        <td>
                            <span class="status-badge-table status-<?= $review['status'] ?>">
                                <?= ucfirst($review['status']) ?>
                            </span>
                        </td>
                        <td><?= date('M d, Y - h:i A', strtotime($review['created_at'])) ?></td>

                        <td>
                            <button onclick='openCommentModal(`<?= addslashes($review["comment"]) ?>`)' 
                                class="btn btn-approve" 
                                style="padding:8px 16px; border-radius:10px;">
                                View
                            </button>
                        </td>

                       
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="no-reviews"><p>No reviews available.</p></div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- COMMENT MODAL -->
<div id="commentModal" class="modal-overlay">
    <div class="modal-box">
        <span class="modal-close" onclick="closeModal()">×</span>
        <h3 style="color:#14532d; margin-bottom:10px;">Review Comment</h3>
        <p id="modalCommentText" style="white-space:pre-line;"></p>
    </div>
</div>

<script>
function showTab(tab) {
    document.querySelectorAll('.tab-content').forEach(t => t.style.display = "none");
    document.querySelectorAll('.tab-button').forEach(b => b.classList.remove("active"));

    document.getElementById(tab + "-tab").style.display = "block";
    event.target.classList.add("active");
}

function openCommentModal(text) {
    document.getElementById("modalCommentText").innerText = text;
    document.getElementById("commentModal").style.display = "flex";
}

function closeModal() {
    document.getElementById("commentModal").style.display = "none";
}

// Default tab:
document.querySelectorAll(".tab-button")[0].click();
</script>

</body>
</html>
