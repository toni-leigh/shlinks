
<div class='admin_left'>
    <div class='panel'>
        <h2>
            <span id='downloadcsv_heading' class='ad_heading_text noselect' onclick='close_height("downloadcsv")'>CSV</span>
            <span id='downloadcsv_show' class='sprite panel_close noselect' onclick='close_height("downloadcsv")'></span>
        </h2>
        <div id='downloadcsv_panel' class='panel_details'>
            <a class='submit' href='/newsletter/csv'>download csv</a>
        </div>
    </div>
    <div class='panel'>
        <h2>
            <span id='signups_heading' class='ad_heading_text noselect' onclick='open_height("signups")'>Chronological List of Sign Ups</span>
            <span id='signups_show' class='sprite panel_open noselect' onclick='open_height("signups")'></span>
        </h2>
        <div id='signups_panel' class='panel_closed panel_details'>
        <?php
            $c=1;
            foreach ($signups as $s)
            {
                $email=$s['email'];
                $time=$s['sign_up_time'];
                ?>
                <div class='signup_row'>
                    <span class='signup_email'><span class='signup_count'><?php echo $c; ?>.</span> <?php echo $email; ?></span>
                    <span class='signup_time'><?php echo $time; ?></span>
                </div>
                <?php
                if (0==$c%10)
                {
                    echo "<div class='signup_spacer'></div>";
                }
                $c++;
            }
        ?>
        </div>
    </div>
</div>
<div class='admin_instructions'>
    <p>
        this is a list of all the sign ups you have had through your website.
    </p>
    <p>
        you can use the <strong>'download csv'</strong> button to get a .csv file
        which is ready to import into a mailshot management service such as mailchimp
    </p>
</div>