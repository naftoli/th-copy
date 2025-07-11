<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

// only super users can see all schools
if ($admin_user['auth'] != 'super') {
    die('You are not authorized to view this page');
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

// Get selected dates and options from form
$start = null;
$end = null;
$wasHebrewMode = false; // Track if Hebrew mode was used
$reportType = isset($_POST['report_type']) ? $_POST['report_type'] : 'medals'; // 'medals' or 'ranks'
$timeFrame = isset($_POST['time_frame']) ? $_POST['time_frame'] : 'specific'; // 'specific' or 'current'

if (isset($_POST['from_hidden']) && isset($_POST['to_hidden'])) {
    $start = $_POST['from_hidden'];
    $end = $_POST['to_hidden'];
    
    // Check if Hebrew dates were used in the previous submission
    if (isset($_POST['was_hebrew_mode']) && $_POST['was_hebrew_mode'] === '1') {
        $wasHebrewMode = true;
    }
}

$medal_data = [];
$rank_data = [];
$grand_totals = [];

// Only query if dates are provided (for specific time frame) or if current totals are requested
if (($start && $end) || $timeFrame === 'current') {
    if ($timeFrame === 'specific' && $start && $end) {
        // convert to julian days for specific date range
        $startArr = explode('-', $start);
        $endArr = explode('-', $end);
        $start_date = gregoriantojd($startArr[1], $startArr[2], $startArr[0]);
        $end_date = gregoriantojd($endArr[1], $endArr[2], $endArr[0]);
    } else {
        // For current totals, use all time (no date filter)
        $start_date = null;
        $end_date = null;
    }

    if ($reportType === 'medals') {
        // Query to get medal data
        $dateFilter = ($timeFrame === 'specific' && $start_date && $end_date) 
            ? "WHERE mm.date_awarded BETWEEN :start_date AND :end_date" 
            : "";
        
        $stmt = $MASHPIA_DB->prepare("
            SELECT 
                school_name, subject_name, medal_name, COUNT(*) AS total,
                s.subject_id, m.medal_ord
            FROM
                medal_marks mm
                    JOIN
                subjects s USING (subject_id)
                    JOIN
                medals m USING (medal_ord)
                    JOIN
                users u USING (user_id)
                    JOIN
                schools sc ON sc.school_id = u.school_id
            " . $dateFilter . "
            GROUP BY sc.school_id , subject_id , medal_ord
        ");
        
        if ($timeFrame === 'specific' && $start_date && $end_date) {
            $stmt->execute([
                'start_date' => $start_date,
                'end_date' => $end_date
            ]);
        } else {
            $stmt->execute();
        }
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $medal_data[$row['school_name']][] = [
                'medal_name' => $row['medal_name'],
                'subject_name' => $row['subject_name'],
                'total' => $row['total'],
                'subject_id' => $row['subject_id'],
                'medal_ord' => $row['medal_ord']
            ];
            if (!isset($grand_totals[$row['subject_name']][$row['medal_name']])) {
                $grand_totals[$row['subject_name']][$row['medal_name']] = [
                    'total' => $row['total'],
                    'subject_id' => $row['subject_id'],
                    'medal_ord' => $row['medal_ord']
                ];
            } else {
                $grand_totals[$row['subject_name']][$row['medal_name']]['total'] += $row['total'];
            }
        }
    } else {
        // Query to get rank data
        $dateFilter = ($timeFrame === 'specific' && $start_date && $end_date) 
            ? "WHERE rm.date_promoted BETWEEN :start_date AND :end_date" 
            : "";
        
        $stmt = $MASHPIA_DB->prepare("
            SELECT 
                school_name, rank_name, COUNT(*) AS total,
                r.rank_ord
            FROM
                rank_marks rm
                    JOIN
                ranks r USING (rank_ord)
                    JOIN
                users u USING (user_id)
                    JOIN
                schools sc ON sc.school_id = u.school_id
            " . $dateFilter . "
            GROUP BY sc.school_id , rank_ord
        ");
        
        if ($timeFrame === 'specific' && $start_date && $end_date) {
            $stmt->execute([
                'start_date' => $start_date,
                'end_date' => $end_date
            ]);
        } else {
            $stmt->execute();
        }
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rank_data[$row['school_name']][] = [
                'rank_name' => $row['rank_name'],
                'total' => $row['total'],
                'rank_ord' => $row['rank_ord']
            ];
            if (!isset($grand_totals['Ranks'][$row['rank_name']])) {
                $grand_totals['Ranks'][$row['rank_name']] = [
                    'total' => $row['total'],
                    'rank_ord' => $row['rank_ord']
                ];
            } else {
                $grand_totals['Ranks'][$row['rank_name']]['total'] += $row['total'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo ucfirst($reportType); ?> Report</title>
    <script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <link href="/heDatePicker/dist/css/he-datepicker.css" rel="stylesheet">
    <script src="/heDatePicker/dist/js/he-datepicker.js"></script>
    <script src="https://code.jquery.com/jquery-1.12.4.min.js" 
        integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" 
        crossorigin="anonymous"></script>
    <style>
        #main {
            margin: 2rem;
            padding: 1rem;
        }

        .school-section {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            padding: 1.5rem;
        }

        .school-header {
            color: #2c3e50;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #3498db;
        }

        .table-container {
            overflow-x: auto;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .table td {
            vertical-align: middle;
        }

        .date-form {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            padding: 1.5rem;
        }

        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .date-type-toggle {
            margin-bottom: 1rem;
        }

        .col-md-4 {
            position: relative;
        }

        .report-options {
            margin-bottom: 1rem;
        }

        .date-inputs {
            display: <?php echo ($timeFrame === 'specific') ? 'block' : 'none'; ?>;
        }

        @media print {
            .school-section {
                background: none;
                border-radius: 0;
                box-shadow: none;
                margin: 0;
                padding: 0;
            }

            .school-section:not(:first-child) {
                page-break-before: always;
            }
            
            .school-section {
                page-break-after: always;
            }
            
            .table tr {
                page-break-inside: avoid;
            }

            .table {
                border-collapse: collapse;
                width: 100% !important;
            }

            .table thead {
                display: table-header-group;
            }

            .container-fluid {
                margin: 0 !important;
                padding: 0 !important;
                max-width: none !important;
                width: 100% !important;
            }
            
            body {
                margin: 0 !important;
                padding: 0 !important;
            }
            
            #main {
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid" style="margin-top: 2rem;">
        <div class="date-form">
            <div class="report-options">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Report Type:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="report_type" id="medals" value="medals" <?php echo ($reportType === 'medals') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="medals">Medals</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="report_type" id="ranks" value="ranks" <?php echo ($reportType === 'ranks') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="ranks">Ranks</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Time Frame:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="time_frame" id="current" value="current" <?php echo ($timeFrame === 'current') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="current">Current Totals (All Time)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="time_frame" id="specific" value="specific" <?php echo ($timeFrame === 'specific') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="specific">Specific Date Range</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <form method="POST" id="dates_form">
                <div class="date-inputs">
                    <div class="date-type-toggle">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="dateTypeToggle">
                            <label class="form-check-label" for="dateTypeToggle">Use Hebrew Dates</label>
                        </div>
                    </div>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="from" class="form-label">From Date</label>
                            <input type="date" class="form-control english-date" id="from" name="from" value="<?php echo $start; ?>" />
                            <input type="text" class="form-control hebrew-date" id="from_he" name="from_he" style="display: none;" autocomplete="off" />
                        </div>
                        <div class="col-md-4">
                            <label for="to" class="form-label">To Date</label>
                            <input type="date" class="form-control english-date" id="to" name="to" value="<?php echo $end; ?>" />
                            <input type="text" class="form-control hebrew-date" id="to_he" name="to_he" style="display: none;" autocomplete="off" />
                        </div>
                    </div>
                </div>
                <div class="row g-3 align-items-end mt-3">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                        <input type="hidden" id="from_hidden" name="from_hidden">
                        <input type="hidden" id="to_hidden" name="to_hidden">
                        <input type="hidden" id="was_hebrew_mode" name="was_hebrew_mode" value="<?php echo $wasHebrewMode ? '1' : '0'; ?>">
                        <input type="hidden" id="report_type_hidden" name="report_type" value="<?php echo $reportType; ?>">
                        <input type="hidden" id="time_frame_hidden" name="time_frame" value="<?php echo $timeFrame; ?>">
                        <?php if (($start && $end) || $timeFrame === 'current'): ?>
                        <button type="button" class="btn btn-primary ms-2" onClick="downloadCSV()">Download CSV</button>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div id='main'></div>

    <script>
        // Initialize date pickers and toggle functionality
        $(document).ready(function() {
            console.log('Document ready');
            
            // Handle report type and time frame changes
            $('input[name="report_type"]').on('change', function() {
                $('#report_type_hidden').val($(this).val());
            });
            
            $('input[name="time_frame"]').on('change', function() {
                $('#time_frame_hidden').val($(this).val());
                if ($(this).val() === 'current') {
                    $('.date-inputs').hide();
                } else {
                    $('.date-inputs').show();
                }
            });
            
            // Initialize Hebrew date pickers with proper callbacks
            const fromDatepicker = new JewishDatepicker('#from_he', {
                hideHeader: true,
                color: '#0d6efd',
                onSelect: function(date, hebrewDate) {
                    console.log('From date selected:', date, hebrewDate);
                    $('#from_he').val(hebrewDate);
                    $('#from_he').attr('data-gregorian-date', date);
                }
            });
            
            const toDatepicker = new JewishDatepicker('#to_he', {
                hideHeader: true,
                color: '#0d6efd',
                onSelect: function(date, hebrewDate) {
                    console.log('To date selected:', date, hebrewDate);
                    $('#to_he').val(hebrewDate);
                    $('#to_he').attr('data-gregorian-date', date);
                }
            });

            // Override the positioning method to fix positioning issues
            function fixDatepickerPosition(datepicker) {
                const originalSetPosition = datepicker.setPostion;
                datepicker.setPostion = function() {
                    const wrapper = this.wrapper;
                    const input = this.element;
                    
                    // Force the wrapper to be positioned correctly
                    wrapper.style.position = 'fixed';
                    wrapper.style.zIndex = '99999';
                    
                    // Get the input's position relative to the viewport
                    const rect = input.getBoundingClientRect();
                    
                    // Calculate positions
                    let left = rect.left;
                    let top = rect.bottom + 5; // 5px gap below input
                    
                    // Get wrapper dimensions (force a reflow to get accurate dimensions)
                    wrapper.style.visibility = 'hidden';
                    wrapper.style.display = 'block';
                    const wrapperWidth = wrapper.offsetWidth;
                    const wrapperHeight = wrapper.offsetHeight;
                    wrapper.style.visibility = 'visible';
                    
                    // Check if it would go off the right edge
                    if (left + wrapperWidth > window.innerWidth) {
                        left = window.innerWidth - wrapperWidth - 10;
                    }
                    
                    // Check if it would go off the bottom edge
                    if (top + wrapperHeight > window.innerHeight) {
                        // Position above the input instead
                        top = rect.top - wrapperHeight - 5;
                    }
                    
                    // Ensure it doesn't go off the left edge
                    if (left < 10) {
                        left = 10;
                    }
                    
                    // Ensure it doesn't go off the top edge
                    if (top < 10) {
                        top = 10;
                    }
                    
                    // Apply the positioning with !important to override CSS
                    wrapper.style.setProperty('left', left + 'px', 'important');
                    wrapper.style.setProperty('top', top + 'px', 'important');
                    wrapper.style.setProperty('position', 'fixed', 'important');
                    wrapper.style.setProperty('z-index', '99999', 'important');
                    
                    console.log('Datepicker positioned at:', left, top, 'for input:', input.id);
                };
                
                // Override the show/hide methods to ensure proper positioning
                const originalShow = datepicker.wrapper.classList.remove.bind(datepicker.wrapper.classList, 'off');
                const originalHide = datepicker.wrapper.classList.add.bind(datepicker.wrapper.classList, 'off');
                
                // Override the click handler to ensure positioning is called
                const input = datepicker.element;
                input.addEventListener('click', function(e) {
                    e.stopPropagation();
                    // Remove off class to show
                    datepicker.wrapper.classList.remove('off');
                    // Force positioning
                    setTimeout(() => datepicker.setPostion(), 10);
                });
                
                // Re-bind the resize event
                window.addEventListener('resize', () => {
                    if (!datepicker.wrapper.classList.contains('off')) {
                        datepicker.setPostion();
                    }
                });
            }
            
            // Apply the positioning fix to both datepickers
            fixDatepickerPosition(fromDatepicker);
            fixDatepickerPosition(toDatepicker);

            // Function to set initial date in datepicker
            function setInitialDate(datepicker, targetDate, inputId) {
                // Wait for the datepicker to be fully initialized
                setTimeout(() => {
                    const wrapper = datepicker.wrapper;
                    const dayElements = wrapper.querySelectorAll('.jewish_datepicker_month_day');
                    
                    dayElements.forEach(dayElement => {
                        const fullDate = dayElement.getAttribute('data-full');
                        if (fullDate) {
                            // Convert DD-MM-YYYY to YYYY-MM-DD for comparison
                            const parts = fullDate.split('-');
                            const formattedDate = `${parts[2]}-${parts[1]}-${parts[0]}`;
                            
                            if (formattedDate === targetDate) {
                                // Remove any existing selections
                                dayElements.forEach(el => el.classList.remove('select'));
                                // Select this day
                                dayElement.classList.add('select');
                                
                                // Update the header display
                                const jDate = wrapper.querySelector('.j_date');
                                const gDate = wrapper.querySelector('.g_date');
                                if (jDate && gDate) {
                                    jDate.innerHTML = dayElement.getAttribute('data-h-full');
                                    gDate.innerHTML = dayElement.getAttribute('data-full').replaceAll('-', '.');
                                }
                            }
                        }
                    });
                }, 1000); // Wait 1 second for initialization
            }

            // Handle date type toggle
            $('#dateTypeToggle').on('change', function() {
                console.log('Toggle changed:', this.checked);
                if (this.checked) {
                    $('.english-date').hide().prop('required', false).prop('disabled', true);
                    $('.hebrew-date').show().prop('required', true).prop('disabled', false);
                } else {
                    $('.english-date').show().prop('required', true).prop('disabled', false);
                    $('.hebrew-date').hide().prop('required', false).prop('disabled', true);
                }
            });

            // Check if Hebrew mode was used in previous submission and automatically enable it
            const wasHebrewMode = $('#was_hebrew_mode').val() === '1';
            if (wasHebrewMode) {
                console.log('Hebrew mode was used previously, enabling Hebrew date mode');
                // First populate Hebrew fields if we have existing dates
                <?php if ($start && $end): ?>
                // Fetch Hebrew dates for existing values and set initial dates
                fetch(`https://www.hebcal.com/converter?cfg=json&date=<?php echo $start; ?>&g2h=1`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.heDateParts) {
                            const he_date = data.heDateParts.d + ' ' + data.heDateParts.m + ' ' + data.heDateParts.y;
                            $('#from_he').val(he_date);
                            $('#from_he').attr('data-gregorian-date', '<?php echo $start; ?>');
                            // Set the initial date in the datepicker
                            setInitialDate(fromDatepicker, '<?php echo $start; ?>', 'from_he');
                        }
                    });
                    
                fetch(`https://www.hebcal.com/converter?cfg=json&date=<?php echo $end; ?>&g2h=1`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.heDateParts) {
                            const he_date = data.heDateParts.d + ' ' + data.heDateParts.m + ' ' + data.heDateParts.y;
                            $('#to_he').val(he_date);
                            $('#to_he').attr('data-gregorian-date', '<?php echo $end; ?>');
                            // Set the initial date in the datepicker
                            setInitialDate(toDatepicker, '<?php echo $end; ?>', 'to_he');
                        }
                    }).then(() => {
                        // After populating Hebrew fields, enable Hebrew mode
                        $('#dateTypeToggle').prop('checked', true).trigger('change');
                    });
                <?php else: ?>
                // No existing dates, just enable Hebrew mode
                $('#dateTypeToggle').prop('checked', true).trigger('change');
                <?php endif; ?>
            } else {
                // Hebrew mode wasn't used previously, but if we have existing dates, populate Hebrew fields for potential use
                <?php if ($start && $end): ?>
                // Fetch Hebrew dates for existing values (for potential future Hebrew mode use)
                fetch(`https://www.hebcal.com/converter?cfg=json&date=<?php echo $start; ?>&g2h=1`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.hebrew) {
                            $('#from_he').val(data.hebrew);
                            $('#from_he').attr('data-gregorian-date', '<?php echo $start; ?>');
                        }
                    });
                    
                fetch(`https://www.hebcal.com/converter?cfg=json&date=<?php echo $end; ?>&g2h=1`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.hebrew) {
                            $('#to_he').val(data.hebrew);
                            $('#to_he').attr('data-gregorian-date', '<?php echo $end; ?>');
                        }
                    });
                <?php endif; ?>
            }

            // Handle form submission
            $('#dates_form').on('submit', function(e) {
                e.preventDefault();
                const isHebrew = $('#dateTypeToggle').is(':checked');
                const timeFrame = $('input[name="time_frame"]:checked').val();
                console.log('Form submitted, isHebrew:', isHebrew, 'timeFrame:', timeFrame);
                
                let from, to;
                if (timeFrame === 'specific') {
                    if (isHebrew) {
                        from = $('#from_he').attr('data-gregorian-date');
                        to = $('#to_he').attr('data-gregorian-date');
                        console.log('Hebrew dates:', from, to);
                    } else {
                        from = $('#from').val();
                        to = $('#to').val();
                        console.log('English dates:', from, to);
                    }
                        
                    if (!from || !to) {
                        alert('Please select both dates');
                        return false;
                    }

                    const fromDate = new Date(from);
                    const toDate = new Date(to);
                    if (toDate < fromDate) {
                        alert('End date must be after or same as start date');
                        return false;
                    }
                    
                    $('#from_hidden').val(from);
                    $('#to_hidden').val(to);
                } else {
                    // Current totals - no dates needed
                    $('#from_hidden').val('');
                    $('#to_hidden').val('');
                }
                
                // Set the Hebrew mode flag based on current selection
                $('#was_hebrew_mode').val(isHebrew ? '1' : '0');
                
                console.log('Submitting with dates:', from, to);
                this.submit();
            });
        });
    </script>

    <script type="text/babel">
        const medalData = <?php echo json_encode($medal_data); ?>;
        const rankData = <?php echo json_encode($rank_data); ?>;
        const grandTotals = <?php echo json_encode($grand_totals); ?>;
        const reportType = "<?php echo $reportType; ?>";
        const timeFrame = "<?php echo $timeFrame; ?>";
        const dateRange = {
            start: "<?php echo $start ? date('F j, Y', strtotime($start)) : ''; ?>",
            end: "<?php echo $end ? date('F j, Y', strtotime($end)) : ''; ?>"
        };

        function downloadCSV() {
            const data = reportType === 'medals' ? medalData : rankData;
            
            if (reportType === 'medals') {
                // Get all unique subject/medal combinations for column headers
                const columns = new Set();
                const columnData = new Map();
                
                Object.values(data).forEach(schoolMedals => {
                    schoolMedals.forEach(medal => {
                        const key = `${medal.subject_name} - ${medal.medal_name}`;
                        columns.add(key);
                        columnData.set(key, {
                            subject_id: medal.subject_id,
                            medal_ord: medal.medal_ord
                        });
                    });
                });
                
                // Sort columns based on subject_id and medal_ord
                const columnArray = Array.from(columns).sort((a, b) => {
                    const dataA = columnData.get(a);
                    const dataB = columnData.get(b);
                    if (dataA.subject_id !== dataB.subject_id) {
                        return dataA.subject_id - dataB.subject_id;
                    }
                    return dataA.medal_ord - dataB.medal_ord;
                });

                // Helper function to escape CSV fields
                const escapeCSV = (field, alwaysQuote = false) => {
                    if (field === null || field === undefined) return '""';
                    const stringField = String(field);
                    if (alwaysQuote || stringField.includes(',') || stringField.includes('"') || stringField.includes('\n')) {
                        return '"' + stringField.replace(/"/g, '""') + '"';
                    }
                    return stringField;
                };

                // Create CSV content
                let csvContent = [
                    "School",
                    ...columnArray,
                    "School Total"
                ].map((field, index) => escapeCSV(field, index === 0)).join(",") + "\n";

                // Add school rows
                Object.entries(data).forEach(([schoolName, medals]) => {
                    const schoolMedalMap = {};
                    let schoolTotal = 0;
                    medals.forEach(medal => {
                        const key = `${medal.subject_name} - ${medal.medal_name}`;
                        schoolMedalMap[key] = parseInt(medal.total, 10);
                        schoolTotal += parseInt(medal.total, 10);
                    });

                    const row = [
                        schoolName,
                        ...columnArray.map(column => schoolMedalMap[column] || 0),
                        schoolTotal
                    ].map((field, index) => escapeCSV(field, index === 0));
                    csvContent += row.join(",") + "\n";
                });

                // Add grand total row
                const grandTotalRow = [
                    "Grand Total",
                    ...columnArray.map(column => {
                        const [subject, medal] = column.split(" - ");
                        return grandTotals[subject]?.[medal]?.total || 0;
                    }),
                    Object.values(grandTotals).reduce((sum, medals) => 
                        sum + Object.values(medals).reduce((a, b) => a + parseInt(b.total, 10), 0), 0
                    )
                ].map((field, index) => escapeCSV(field, index === 0));
                csvContent += grandTotalRow.join(",");
            } else {
                // Ranks CSV
                const columns = new Set();
                const columnData = new Map();
                
                Object.values(data).forEach(schoolRanks => {
                    schoolRanks.forEach(rank => {
                        columns.add(rank.rank_name);
                        columnData.set(rank.rank_name, {
                            rank_ord: rank.rank_ord
                        });
                    });
                });
                
                // Sort columns based on rank_ord
                const columnArray = Array.from(columns).sort((a, b) => {
                    const dataA = columnData.get(a);
                    const dataB = columnData.get(b);
                    return dataA.rank_ord - dataB.rank_ord;
                });

                const escapeCSV = (field, alwaysQuote = false) => {
                    if (field === null || field === undefined) return '""';
                    const stringField = String(field);
                    if (alwaysQuote || stringField.includes(',') || stringField.includes('"') || stringField.includes('\n')) {
                        return '"' + stringField.replace(/"/g, '""') + '"';
                    }
                    return stringField;
                };

                let csvContent = [
                    "School",
                    ...columnArray,
                    "School Total"
                ].map((field, index) => escapeCSV(field, index === 0)).join(",") + "\n";

                Object.entries(data).forEach(([schoolName, ranks]) => {
                    const schoolRankMap = {};
                    let schoolTotal = 0;
                    ranks.forEach(rank => {
                        schoolRankMap[rank.rank_name] = parseInt(rank.total, 10);
                        schoolTotal += parseInt(rank.total, 10);
                    });

                    const row = [
                        schoolName,
                        ...columnArray.map(column => schoolRankMap[column] || 0),
                        schoolTotal
                    ].map((field, index) => escapeCSV(field, index === 0));
                    csvContent += row.join(",") + "\n";
                });

                const grandTotalRow = [
                    "Grand Total",
                    ...columnArray.map(column => grandTotals['Ranks']?.[column]?.total || 0),
                    Object.values(grandTotals['Ranks'] || {}).reduce((sum, rank) => 
                        sum + parseInt(rank.total, 10), 0
                    )
                ].map((field, index) => escapeCSV(field, index === 0));
                csvContent += grandTotalRow.join(",");
            }

            // Create and trigger download
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            const url = URL.createObjectURL(blob);
            const filename = `${reportType}_report_${timeFrame === 'current' ? 'current_totals' : dateRange.start + '_to_' + dateRange.end}.csv`;
            link.setAttribute("href", url);
            link.setAttribute("download", filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function Table() {
            const data = reportType === 'medals' ? medalData : rankData;
            const reportTitle = reportType === 'medals' ? 'Medal Report' : 'Rank Report';
            
            if (timeFrame === 'specific' && (!dateRange.start || !dateRange.end)) {
                return (
                    <div className="container-fluid">
                        <div className="school-section">
                            <h3 className="school-header">Please select a date range to generate the report</h3>
                        </div>
                    </div>
                );
            }

            if (reportType === 'medals') {
                // Get all unique subject/medal combinations for column headers
                const columns = new Set();
                const columnData = new Map();
                
                Object.values(data).forEach(schoolMedals => {
                    schoolMedals.forEach(medal => {
                        const key = `${medal.subject_name} - ${medal.medal_name}`;
                        columns.add(key);
                        columnData.set(key, {
                            subject_id: medal.subject_id,
                            medal_ord: medal.medal_ord
                        });
                    });
                });
                
                // Sort columns based on subject_id and medal_ord
                const columnArray = Array.from(columns).sort((a, b) => {
                    const dataA = columnData.get(a);
                    const dataB = columnData.get(b);
                    if (dataA.subject_id !== dataB.subject_id) {
                        return dataA.subject_id - dataB.subject_id;
                    }
                    return dataA.medal_ord - dataB.medal_ord;
                });

                return (
                    <div className="container-fluid">
                        <div className="school-section">
                            <h3 className="school-header">
                                {reportTitle} <small className="text-muted">
                                    ({timeFrame === 'current' ? 'Current Totals' : dateRange.start + ' to ' + dateRange.end})
                                </small>
                            </h3>
                            <div className="table-container">
                                <table className="table table-striped table-hover table-bordered">
                                    <thead className="table-light">
                                        <tr>
                                            <th scope="col">School</th>
                                            {columnArray.map((column, index) => (
                                                <th key={index} scope="col">{column}</th>
                                            ))}
                                            <th scope="col">School Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {Object.entries(data).map(([schoolName, medals], index) => {
                                            // Create a map of subject-medal combinations to totals for this school
                                            const schoolMedalMap = {};
                                            let schoolTotal = 0;
                                            medals.forEach(medal => {
                                                const key = `${medal.subject_name} - ${medal.medal_name}`;
                                                schoolMedalMap[key] = parseInt(medal.total, 10);
                                                schoolTotal += parseInt(medal.total, 10);
                                            });

                                            return (
                                                <tr key={index}>
                                                    <td>{schoolName}</td>
                                                    {columnArray.map((column, colIndex) => (
                                                        <td key={colIndex}>{schoolMedalMap[column] || 0}</td>
                                                    ))}
                                                    <td className="fw-bold">{schoolTotal}</td>
                                                </tr>
                                            );
                                        })}
                                        <tr className="table-secondary">
                                            <td className="fw-bold">Grand Total</td>
                                            {columnArray.map((column, index) => {
                                                const [subject, medal] = column.split(" - ");
                                                return (
                                                    <td key={index} className="fw-bold">
                                                        {grandTotals[subject]?.[medal]?.total || 0}
                                                    </td>
                                                );
                                            })}
                                            <td className="fw-bold">
                                                {Object.values(grandTotals).reduce((sum, medals) => 
                                                    sum + Object.values(medals).reduce((a, b) => a + parseInt(b.total, 10), 0), 0
                                                )}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                );
            } else {
                // Ranks table
                const columns = new Set();
                const columnData = new Map();
                
                Object.values(data).forEach(schoolRanks => {
                    schoolRanks.forEach(rank => {
                        columns.add(rank.rank_name);
                        columnData.set(rank.rank_name, {
                            rank_ord: rank.rank_ord
                        });
                    });
                });
                
                // Sort columns based on rank_ord
                const columnArray = Array.from(columns).sort((a, b) => {
                    const dataA = columnData.get(a);
                    const dataB = columnData.get(b);
                    return dataA.rank_ord - dataB.rank_ord;
                });

                return (
                    <div className="container-fluid">
                        <div className="school-section">
                            <h3 className="school-header">
                                {reportTitle} <small className="text-muted">
                                    ({timeFrame === 'current' ? 'Current Totals' : dateRange.start + ' to ' + dateRange.end})
                                </small>
                            </h3>
                            <div className="table-container">
                                <table className="table table-striped table-hover table-bordered">
                                    <thead className="table-light">
                                        <tr>
                                            <th scope="col">School</th>
                                            {columnArray.map((column, index) => (
                                                <th key={index} scope="col">{column}</th>
                                            ))}
                                            <th scope="col">School Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {Object.entries(data).map(([schoolName, ranks], index) => {
                                            const schoolRankMap = {};
                                            let schoolTotal = 0;
                                            ranks.forEach(rank => {
                                                schoolRankMap[rank.rank_name] = parseInt(rank.total, 10);
                                                schoolTotal += parseInt(rank.total, 10);
                                            });

                                            return (
                                                <tr key={index}>
                                                    <td>{schoolName}</td>
                                                    {columnArray.map((column, colIndex) => (
                                                        <td key={colIndex}>{schoolRankMap[column] || 0}</td>
                                                    ))}
                                                    <td className="fw-bold">{schoolTotal}</td>
                                                </tr>
                                            );
                                        })}
                                        <tr className="table-secondary">
                                            <td className="fw-bold">Grand Total</td>
                                            {columnArray.map((column, index) => (
                                                <td key={index} className="fw-bold">
                                                    {grandTotals['Ranks']?.[column]?.total || 0}
                                                </td>
                                            ))}
                                            <td className="fw-bold">
                                                {Object.values(grandTotals['Ranks'] || {}).reduce((sum, rank) => 
                                                    sum + parseInt(rank.total, 10), 0
                                                )}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                );
            }
        }

        const container = document.getElementById('main');
        const root = ReactDOM.createRoot(container);
        root.render(<Table />);
    </script>
</body>
</html>
