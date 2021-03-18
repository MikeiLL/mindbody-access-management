<?php

use MzMboAccess\Core as Core;

?>
    <div id="mzLogInContainer">
    
        <form role="form" class="form-group" style="margin:1em 0;" data-async id="mzLogIn" data-target="#mzSignUpModal" method="POST">

            <h3><?php echo $data->atts['call_to_action']; ?></h3>

            <input type="hidden" name="nonce" value="<?php echo $data->signup_nonce; ?>"/>

            <input type="hidden" name="siteID" value="<?php echo $data->siteID; ?>" />

            <div class="row">

                <div class="form-group col-xs-8 col-sm-6">

                    <label for="username">Email</label>

                    <input type="email" size="10" class="form-control" id="email" name="email" placeholder="<?php echo $data->email; ?>" required>

                </div>

            </div>

            <div class="row">

                <div class="form-group col-xs-8 col-sm-6">

                    <label for="password">Password</label>

                    <input type="password" size="10" class="form-control" name="password" id="password" placeholder="<?php echo $data->password; ?>" required>

                </div>

            </div>

            <div class="row" style="margin:.5em;">

                <div class="col-12">

                    <button type="submit" class="btn btn-primary btn-xs"><?php echo $data->login; ?></button>
                
                    <?php if (! empty($data->password_reset_request)) : ?>
                    <a style="text-decoration:none;" href="https://clients.mindbodyonline.com/PasswordReset?studioid=<?php echo $data->siteID; ?>" class="btn btn-primary btn-xs" target="_blank"><?php echo $data->password_reset_request; ?></a>

                    <?php endif; ?>
                    
                    <?php if (! empty($data->manage_on_mbo)) : ?>
                    <a style="text-decoration:none;" href="https://clients.mindbodyonline.com/ws.asp?&amp;sLoc=1&studioid=<?php echo $data->siteID; ?>" class="btn btn-primary btn-xs" id="MBOSite" target="_blank"><?php echo $data->manage_on_mbo; ?></a>
                    
                    <?php endif; ?>
                    
                </div>
            
            </div>

        </form>

    </div>
