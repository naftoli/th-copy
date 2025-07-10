<?
$admin_auth = array('school'); 
require('db.php');

require_once 'ranksEarned.class.php';
$m = new RanksEarned;
$report = $m->getReport();
$ranks = $m->getRanks();
//echo "<pre>"; print_r($report); echo "</pre>"; exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranks Earned Summary</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .table th {
            border: none;
            font-weight: 600;
            padding: 1rem;
        }
        .table td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .year-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            text-align: center;
            font-weight: 600;
            font-size: 1.2rem;
        }
        .rank-row {
            background-color: #f8f9fa;
            font-weight: 500;
        }
        .rank-count {
            font-weight: 600;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">
                    <i class="fas fa-star me-3"></i>Ranks Earned Summary
                </h1>
                
                <?php foreach ($report as $year => $arr) { ?>
                    <div class="year-section mb-4">
                        <div class="year-header">
                            <i class="fas fa-calendar-alt me-2"></i><?php echo $year; ?>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-star me-2"></i>Rank</th>
                                        <th class="text-center"><i class="fas fa-users me-2"></i>Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ranks as $ord => $rank) { ?>
                                        <tr class="rank-row">
                                            <td><strong><?php echo $rank; ?></strong></td>
                                            <td class="text-center rank-count">
                                                <?php echo $report[$year][$ord] ?? '0'; ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>