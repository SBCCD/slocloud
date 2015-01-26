SLO Cloud
========

A Student Learning Objectives (SLO) Reporting Tool. [Click here](http://lawsonry.com/projects/slocloud) to access the demo.

**This is a peer-reviewed, open-source project for public institutions of higher education, licensed under the GPLv2.**

# About

The SLO Cloud was conceptualized by Aeron Zentner during his time at Lassen College and engineered by Jesse Lawson when both worked at Coastline College.

A full background and development writeup is available online at [lawsonry.com/slocloud](http://lawsonry.com/slocloud).

## About this Release

The code in this release represents a prototype model of what an SLO Cloud could be for any school. In this demo, all configuration values are hard-coded in a `config.php` file, each variable semantically named so that they're easily explained. 

## Requirements

* Intermediate PHP, JavaScript, HTML, and CSS
* Any server with PHP enabled

# Instructions

1. Download the zip file and unpack it into a web-accessable directory on a server with PHP enabled. 

2. Navigate to the folder and enjoy!

# Customization

This release is avilable to customize as-is via hard-coded values, or (and this is much more recommended), you can access the `/api` folder for an example of how you could use the Slim PHP framework to setup a REST API to communicate with a MySQL backend. The interface methodology would look something like this:

`SLO Cloud -> MySQL -> YOUR CUSTOM SERVER -> DataTel/CurricuNet/etc`

Of course, this is just one of many ways that customization is possible. 

# Demonstration

SBCCD has a brief write-up of how they customized the SLOCloud for their usage [here](http://tess.sbccd.org/Departments/District%20Computing%20Services/SLOCloud).

# Contributing

If you are a programmer, developer, or other technology professional looking to contribute to this project, please feel free to fork this repo and submit pull requests at will. All pull requests are reviewed at least weekly. 

## Code Commenting Policy

For those of you who are actively contributing to projects, please comment your code as verbose as possible and add an identifier and timestamp to your code comments.

For example, in the comment head of the file you're working on, add something like this:

	Contributors:
	Jesse Lawson (lawsonry), jesse@lawsonry.com


Then, in your code, put something like this:

`// (10 Oct 2014 lawsonry) Here is a comment for the code below`

Thank you!
