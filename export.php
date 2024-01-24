<?php

ob_start();

$jsonContent = file_get_contents('evaluation-20190711.json');

$data = json_decode($jsonContent, true);

echo '<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">';


echo '<div class="overflow-x-auto">';
echo '<table class="min-w-full bg-white divide-y divide-gray-200 table-fixed">';
echo '<thead class="bg-gray-50">';
echo '<tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>';
echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age</th>';
echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>';
echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Working Status</th>';
echo '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th></tr>';
echo '</thead>';
echo '<tbody class="bg-white divide-y divide-gray-200">';

foreach ($data['data'] as $row) {
    $dateOfBirth = new DateTime($row['date_of_birth'], new DateTimeZone('UTC'));
    $dateOfBirth->setTimezone(new DateTimeZone('Asia/Kuala_Lumpur'));
    $age = $dateOfBirth->diff(new DateTime('now', new DateTimeZone('Asia/Kuala_Lumpur')))->y;
    $workingStatus = $row['working_status'] ? 'Yes' : 'No';
    
    echo "<tr>";
    echo "<td class='px-6 py-4 whitespace-nowrap'>{$row['name']}</td>";
    echo "<td class='px-6 py-4 whitespace-nowrap'>{$age} years</td>";
    echo "<td class='px-6 py-4 whitespace-nowrap'>{$row['address']}</td>";
    echo "<td class='px-6 py-4 whitespace-nowrap'>{$workingStatus}</td>";
    echo '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium"><a href="export.php?id=' . $row['id'] . '" class="text-indigo-600 hover:text-indigo-900">Export CSV</a></td>';
    echo "</tr>";
}

echo '</tbody>';
echo '</table>';
echo '</div>';



if (isset($_GET['id'])) {
    ob_end_clean();

    $id = $_GET['id'];
    foreach ($data['data'] as $row) {
        if ($row['id'] == $id) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="export.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');

            $fh = fopen('php://output', 'w');
            fputcsv($fh, ['Title', 'Test', 'Score', 'Evaluated At']);

            $evaluatedAt = new DateTime($row['evaluation']['created_at'], new DateTimeZone('UTC'));
            $evaluatedAt->setTimezone(new DateTimeZone('Asia/Kuala_Lumpur'));
            $formattedEvaluatedAt = $evaluatedAt->format('Y-m-d');

            foreach ($row['evaluation']['score'] as $testIndex => $scoreData) {
                $testName = 'test_' . ($testIndex + 1);
                $score = $scoreData[$testName];
                fputcsv($fh, [$row['evaluation']['title'], $testName, $score, $formattedEvaluatedAt]);
            }

            fclose($fh);
            exit;
        }
    }
    ob_start();
}

ob_end_flush();
?>
