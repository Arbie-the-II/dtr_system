<?php
require __DIR__ . '/vendor/autoload.php';

// Load the credentials file
$client = new Google_Client();
$client->setApplicationName('Google Sheets API PHP Quickstart');
$client->setScopes(Google_Service_Sheets::SPREADSHEETS);
$client->setAuthConfig('credentials.json');
$client->setAccessType('offline');
$client->setPrompt('select_account consent');

// Load previously authorized token
$tokenPath = 'token.json';
if (file_exists($tokenPath)) {
    $accessToken = json_decode(file_get_contents($tokenPath), true);
    $client->setAccessToken($accessToken);
}

// If no token or token is expired, get a new one
if ($client->isAccessTokenExpired()) {
    if ($client->getRefreshToken()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
    } else {
        $authUrl = $client->createAuthUrl();
        printf("Open the following link in your browser:\n%s\n", $authUrl);
        print 'Enter verification code: ';
        $authCode = trim(fgets(STDIN));

        // Exchange auth code for access token
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
        $client->setAccessToken($accessToken);

        // Save token
        if (!file_exists(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0700, true);
        }
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    }
}

$service = new Google_Service_Sheets($client);

// Replace with your Google Sheet ID
$spreadsheetId = 'YOUR_SPREADSHEET_ID'; // e.g., 1aBcD...
$range = 'Sheet1!A1:D1'; // Starting cell

$values = [
    ["Intern ID", "Name", "School", "Hours Left"]
];
$body = new Google_Service_Sheets_ValueRange([
    'values' => $values
]);

$params = ['valueInputOption' => 'RAW'];
$result = $service->spreadsheets_values->update($spreadsheetId, $range, $body, $params);
printf("%d cells updated.", $result->getUpdatedCells());
