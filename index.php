<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <title>Hello, world!</title>
</head>
<body>
<div class="container">
    <h1>
        Companies House Address Search
    </h1>
    <h2>
        Enter the company name and officer of the company as registered with companies house to return the company's registered address.
    </h2>
    <form method="post" action="/">
        <div class="form-group">
            <label for="companyNameHelp">Company name</label>
            <input type="text" class="form-control" id="companyName" name="companyName" aria-describedby="companyNameHelp" placeholder="A Fake Company Ltd">
            <small id="companyNameHelp" class="form-text text-muted">The company name as is registered with Companies House.</small>
        </div>
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <label for="firstNameHelp">First name</label>
                    <input type="text" class="form-control" id="firstName" name="firstName" aria-describedby="firstNameHelp" placeholder="First name">
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <label for="lastNameHelp">Last name</label>
                    <input type="text" class="form-control" id="lastName" name="lastName" aria-describedby="lastNameHelp" placeholder="Last name">
                </div>
            </div>
        </div>


<?php

require __DIR__.'/test/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

if ($_POST['companyName']) {
    if ($_POST['firstName'] && $_POST['lastName']) {
        $client = new Client([
            'headers' => [
                'Authorization' => 'Basic '.base64_encode('AA6ix0av3oAffl8mAde-WtbXoPyp8FsAzcVsw1ud'.':'),
                'Content-Type' => 'application/json'
            ]
        ]);

        $companyExists = false;
        $done = false;
        $companyName = '';
        $companyAddressSnippet = null;

            $url = 'https://api.companieshouse.gov.uk/search/companies';
            try {
                $response = $client->request('GET', $url, [
                    'query' => [
                        'q' => $_POST['companyName'],
                    ]
                ]);
            } catch (GuzzleException $e) {
                exit();
            }
            $content = $response->getBody()->getContents();
            $payload = json_decode($content);
            $companies = $payload->items;
            $totalCompaniesResults = $payload->total_results;
            foreach ($companies as $company) {
                if ($company->title === $_POST['companyName']) {
                    $companyExists = true;
                    $companyName = $company->title;
                    $companyAddressSnippet = $company->address_snippet;

                    $url = 'https://api.companieshouse.gov.uk/company/' . $company->company_number . '/officers';
                    $response = $client->request('GET', $url, [
                        'query' => [
                            //
                        ]
                    ]);

                    $ct = $response->getBody()->getContents();
                    $payload = json_decode($ct);
                    $officers = $payload->items;
                    $totalOfficersResults = $payload->total_results;
                    foreach ($officers as $officer) {
                        if (false !== strpos($officer->name, $_POST['firstName'] . ' ' . $_POST['lastName'])) {
                            $done = true;
                            break;
                        }
                    }

                    if ($done) {
                        break;
                    }
                }

            }
    } else {
        echo '<div class="alert alert-danger" role="alert">
            Please input first name and last name.
        </div>';
    }
} else {
    echo '<div class="alert alert-danger" role="alert">
            Please input company name.
        </div>';
}

if ($companyExists === false) {
    echo '<div class="alert alert-danger" role="alert">
            A record for "' . $_POST['companyName'] . '" could not be found at Companies House.
        </div>';
} else {
    if ($done === false) {
        echo '<div class="alert alert-danger" role="alert">
                "' . $_POST['firstName'] . ' ' . $_POST['lastName'] . '" could not be found as an officer of ' . $companyName . '.
            </div>';
    }
}
if ($companyExists === true && $done === true) {
    echo '<div class="alert alert-success" role="alert">
              ' . $companyAddressSnippet . '
            </div>';
}

?>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>
