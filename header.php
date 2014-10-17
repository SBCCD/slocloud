<?php
/* Remember that this is going to be called from a sub-folder, so all the URLs need to be changed to add the ../ in front of them */

session_start();

// First we'll determine if we're at our home page or if we're at a client site.

// Remove first character (which is a /)
$where = $_SERVER['REQUEST_URI']."config.php";

// We're at a client site, so let's see if we have a valid url
if( file_exists($where)) {
    // That requested url is valid, so let's pull it in
    // Do nothing here -- the file was included already
    include $where;
} else {
    // Oops! That url doesn't work, so let's redirect to our 404 page
    header("Location: http://slocloud.pragmads.com/404.html#no-config-file-found-for-".$where);
}

// Each institution will have its own customization file.
require_once("config.php");

global $config;

?>

<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html lang="en" class="no-js"> <!--<![endif]-->
<head>
    <title>SLOCloud&trade; by PragmaDS</title>

    <meta charset="utf-8">
    <base href="http://slocloud.pragmads.com/" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Google Font: Open Sans -->
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:400,400italic,600,600italic,800,800italic">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Oswald:400,300,700">

    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="../css/font-awesome.min.css">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../css/bootstrap.min.css">

    <!-- App CSS -->
    <link rel="stylesheet" href="../css/mvpready-admin.css">
    <link rel="stylesheet" href="../css/mvpready-flat.css">
    <!-- <link href="../css/custom.css" rel="stylesheet">-->

    <!-- Favicon -->
    <link rel="shortcut icon" href="favicon.ico">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>

<body class=" ">

    <div id="wrapper">

        <header role="banner">

            <div class="container">

                <div class="header-navbar">

                    <h1 class="toptitle"><center><img src="../img/slocloud-badge-trans.png" style="height: 35px; width: auto; margin-bottom: 10px" /> <?php echo $config["institutionName"]; ?></center></h1>

		<p><a href="http://lawsonry.com/projects/slocloud/demo">Faculty Page</a> | <a href="http://lawsonry.com/projects/slocloud/demo/dashboard.php">SLO Reports</a> | <a href="http://lawsonry.com/projects/slocloud/demo/programs.php">Program SLO Reports</a></p>

                </div> <!-- /.navbar-header -->

            </div> <!-- /.container -->

        </header>


        <?php /*<div class="mainnav">

            <div class="container">

                <a class="mainnav-toggle" data-toggle="collapse" data-target=".mainnav-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <i class="fa fa-bars"></i>
                </a>

                <nav class="collapse mainnav-collapse" role="navigation">

                    <form class="mainnav-form pull-right" role="search">
                        <input type="text" class="form-control input-md mainnav-search-query" placeholder="Search">
                        <button class="btn btn-sm mainnav-form-btn"><i class="fa fa-search"></i></button>
                    </form>

                    <ul class="mainnav-menu">

                        <li class="dropdown active">
                            <a href="../index.html" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown">
                                Dashboards
                                <i class="mainnav-caret"></i>
                            </a>

                            <ul class="dropdown-menu" role="menu">
                                <li>
                                    <a href="../index.html">
                                        <i class="fa fa-dashboard"></i>
                                        &nbsp;&nbsp;Analytics Dashboard
                                    </a>
                                </li>

                                <li>
                                    <a href="../dashboard-2.html">
                                        <i class="fa fa-dashboard"></i>
                                        &nbsp;&nbsp;Sidebar Dashboard
                                    </a>
                                </li>

                                <li>
                                    <a href="../dashboard-3.html">
                                        <i class="fa fa-dashboard"></i>
                                        &nbsp;&nbsp;Reports Dashboard
                                    </a>
                                </li>
                            </ul>
                        </li>


                        <li class="dropdown ">

                            <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown">
                                Components
                                <i class="mainnav-caret"></i>
                            </a>

                            <ul class="dropdown-menu" role="menu">

                                <li>
                                    <a href="../components-tabs.html">
                                        <i class="fa fa-bars"></i>
                                        &nbsp;&nbsp;Tabs &amp; Accordions
                                    </a>
                                </li>

                                <li>
                                    <a href="../components-popups.html">
                                        <i class="fa fa-calendar-o"></i>
                                        &nbsp;&nbsp;Popups &amp; Alerts
                                    </a>
                                </li>

                                <li>
                                    <a href="../components-validation.html">
                                        <i class="fa fa-check"></i>
                                        &nbsp;&nbsp;Validation
                                    </a>
                                </li>

                                <li>
                                    <a href="../components-datatables.html">
                                        <i class="fa fa-table"></i>
                                        &nbsp;&nbsp;Data Tables
                                    </a>
                                </li>

                                <li>
                                    <a href="../components-gallery.html">
                                        <i class="fa fa-picture-o"></i>
                                        &nbsp;&nbsp;Gallery
                                    </a>
                                </li>

                                <li>
                                    <a href="../components-charts.html">
                                        <i class="fa fa-bar-chart-o"></i>
                                        &nbsp;&nbsp;Charts
                                    </a>
                                </li>
                            </ul>
                        </li>


                        <li class="dropdown ">

                            <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown">
                                Sample Pages
                                <i class="mainnav-caret"></i>
                            </a>

                            <ul class="dropdown-menu" role="menu">
                                <li>
                                    <a href="../page-pricing.html">
                                        <i class="fa fa-money"></i>
                                        &nbsp;&nbsp;Plans & Billing
                                    </a>
                                </li>

                                <li>
                                    <a href="../page-profile.html">
                                        <i class="fa fa-user"></i>
                                        &nbsp;&nbsp;Profile
                                    </a>
                                </li>

                                <li>
                                    <a href="../page-settings.html">
                                        <i class="fa fa-cogs"></i>
                                        &nbsp;&nbsp;Settings
                                    </a>
                                </li>

                                <li>
                                    <a href="../page-faq.html">
                                        <i class="fa fa-question"></i>
                                        &nbsp;&nbsp;FAQ
                                    </a>
                                </li>
                            </ul>
                        </li>


                        <li class="dropdown ">

                            <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown">
                                Extras
                                <i class="mainnav-caret"></i>
                            </a>

                            <ul class="dropdown-menu" role="menu">
                                <li>
                                    <a href="../page-notifications.html">
                                        <i class="fa fa-bell"></i>
                                        &nbsp;&nbsp;Notifications
                                    </a>
                                </li>

                                <li>
                                    <a href="../extras-icons.html">
                                        <i class="fa fa-smile-o"></i>
                                        &nbsp;&nbsp;Font Icons
                                    </a>
                                </li>

                                <li class="dropdown-submenu">
                                    <a tabindex="-1" href="#">
                                        <i class="fa fa-ban"></i>
                                        &nbsp;&nbsp;Error Pages
                                    </a>

                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="../page-404.html">
                                                <i class="fa fa-ban"></i>
                                                &nbsp;&nbsp;404 Error
                                            </a>
                                        </li>

                                        <li>
                                            <a href="../page-500.html">
                                                <i class="fa fa-ban"></i>
                                                &nbsp;&nbsp;500 Error
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="dropdown-submenu">

                                    <a tabindex="-1" href="#">
                                        <i class="fa fa-lock"></i>
                                        &nbsp;&nbsp;Login Pages
                                    </a>

                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="../account-login.html">
                                                <i class="fa fa-unlock"></i>
                                                &nbsp;&nbsp;Login
                                            </a>
                                        </li>

                                        <li>
                                            <a href="../account-login-social.html">
                                                <i class="fa fa-unlock"></i>
                                                &nbsp;&nbsp;Login Social
                                            </a>
                                        </li>

                                        <li>
                                            <a href="../account-signup.html">
                                                <i class="fa fa-star"></i>
                                                &nbsp;&nbsp;Signup
                                            </a>
                                        </li>

                                        <li>
                                            <a href="../account-forgot.html">
                                                <i class="fa fa-envelope"></i>
                                                &nbsp;&nbsp;Forgot Password
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                            </ul>

                        </li>

                    </ul>

                </nav>

            </div> <!-- /.container -->

        </div> <!-- /.mainnav -->*/ ?>

