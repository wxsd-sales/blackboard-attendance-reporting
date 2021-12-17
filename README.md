Blackboard Attendance Reporting
===============================
**Post Webex meeting attendance to your courses on Blackboard.**

This proof-of-concept application lets you update Blackboard attendance records based on who joined your Webex meeting.
The target audience for this PoC are instructors who want a seamless way to take attendance for their virtual classes.

<!--
<p align="center">
   <a href="https://www.youtube.com/watch?v=lKNUpkCK6uI&t=87s" target="_blank" alt="See the video demo.">
       <img src="https://user-images.githubusercontent.com/6129517/144125345-dda6e239-a271-478e-ac41-ac28d74832a6.gif" alt="azure-group-sync-demo"/>
    </a>
</p>
-->

<!-- ⛔️ MD-MAGIC-EXAMPLE:START (TOC:collapse=true&collapseText=Click to expand) -->
<details>
<summary>Table of Contents (click to expand)</summary>

* [Overview](#overview)
* [Setup](#setup)
* [Demo](#demo)
* [Support](#support)

</details>
<!-- ⛔️ MD-MAGIC-EXAMPLE:END -->

## Overview

The application retrieves two lists:
- The participants for each Webex meeting (where the logged-in user is a host)
- The students in each Blackboard course (where the logged-in user is an instructor)

The front end serves as a way to "map" a Webex meeting to a Blackboard course.

When a Webex meeting is mapped to a Blackboard course, the application updates Blackboard attendance records for the course by comparing the two lists of users.


## Setup

These instructions assume that you have:
- Administrator access to a Blackboard Learn instance and Webex Control Hub.
- [Docker installed](https://docs.docker.com/engine/install/) and running on a Windows (via WSL2), macOS, or Linux machine.

Open a new terminal window and follow the instructions below.

1. Clone this repository and change directory
   ```
   git clone https://github.com/WXSD-Sales/blackboard-attendance-reporting && cd blackboard-attendance-reporting
   ```

2. Rename `.env.example` file to `.env` (you may also edit your database credentials within this renamed file)
   ```
   mv .env.example .env
   ```

3. Review and follow the [Three-legged OAuth](https://help.blackboard.com/Learn/Administrator/Hosting/System_Integration/Building_Blocks_and_REST_APIs/Three_Legged_OAuth) guide to create and register a Blackboard Integration.
   Take note of your Blackboard Subdomain, Application ID and Application Secret. Assign these values to the `BLACKBOARD_SUBDOMAIN`, `BLACKBOARD_CLIENT_ID`, and `BLACKBOARD_CLIENT_SECRET` environment variables within the `.env` file respectively.

4. Review and follow the [Registering your Integration on Webex](https://developer.webex.com/docs/integrations#registering-your-integration) guide.
    - Your registration must have the following [Webex REST API scopes](https://developer.webex.com/docs/integrations#scopes):
      | Scope                     | Description                                    |
      |---------------------------|------------------------------------------------|
      | spark-admin:people_read   | Access to read your user's company directory   |
      | meeting:schedules_read    | Retrieve your Webex meeting lists and details  |
      | meeting:participants_read | Read participant information from meetings     |
      | spark:kms                 | Permission to interact with encrypted content  |
    - Use these Redirect URIs:
        - `https://localhost/auth/webex/callback`
        - `http://localhost/auth/webex/callback`
    - Take note of your Client ID and Client Secret. Assign these values to the `WEBEX_CLIENT_ID` and `WEBEX_CLIENT_SECRET` environment variables within the `.env` file respectively.

5. Review and follow the [Creating a Webex Bot](https://developer.webex.com/docs/bots#creating-a-webex-bot) guide to create a Webex Bot. Take note of your Bot ID and Bot access token. Assign these values to the `WEBEX_BOT_ID` and `WEBEX_BOT_TOKEN` environment variables within the `.env` file respectively.

6. Start the Docker development environment via [Laravel Sail](https://laravel.com/docs/8.x/sail)
   ```
   ./vendor/bin/sail up -d
   ```

7. Run [Laravel Mix](https://laravel.com/docs/8.x/mix)  
   When you run this command, the application's CSS and JavaScript assets will be compiled and placed in the application's public directory:
   ```
   ./vendor/bin/sail npm run dev
   ```

8. Run the Scheduler locally  
   This command will run in the foreground and invoke the scheduler every minute until you terminate the command. In a new terminal window:
   ```
   ./vendor/bin/sail php artisan schedule:work
   ```

9. Run the Queue Worker  
   Start a queue worker and process new jobs as they are pushed onto the queue. This command will continue to run until it is manually stopped or you close your terminal. In a new terminal window:
   ```
   ./vendor/bin/sail php artisan queue:work
   ```

Instructors can now navigate to `http://localhost` where they will see their most recent Webex meetings and Blackboard courses.
To post attendance records on Blackboard, they should manually assign a meeting to a course and provide confirmation.


## Demo

A video where I demo this PoC is available on YouTube — https://www.youtube.com/watch?v=&t=s.


## Support

Please reach out to the WXSD team at [wxsd@external.cisco.com](mailto:wxsd@external.cisco.com?cc=ashessin@cisco.com&subject=Early%20One%20Button%20to%20Push) or contact me on Webex (ashessin@cisco.com).
