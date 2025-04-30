<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Two-Factor Authentication</h3>
                    <p class="text-center">Please enter the 6-digit code sent to your email</p>
                    
                    <?php if ($controller->has_verification_failed()): ?>
                    <div class="alert alert-danger" role="alert">
                        Invalid verification code. Please try again.
                    </div>
                    <?php endif; ?>

                    <?php if ($controller->was_code_resent()): ?>
                    <div class="alert alert-success" role="alert">
                        A new code has been sent to your email.
                    </div>
                    <?php endif; ?>

                    <form method="post" id="selfhelp-2fa-form">
                        <input type="hidden" name="type" value="2fa_verify">
                        <div class="d-flex justify-content-center mb-4">
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                            <input type="text" 
                                   name="digit_<?php echo $i; ?>" 
                                   class="form-control mx-1 text-center selfhelp-2fa-input font-weight-bold h4" 
                                   style="width: 45px; height: 50px;" 
                                   maxlength="1" 
                                   required 
                                   pattern="[0-9]"
                                   inputmode="numeric">
                            <?php endfor; ?>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary px-4">Verify</button>
                        </div>
                    </form>

                    <div class="mt-4 text-center">
                        <p class="mb-2">Didn't receive the code?</p>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="type" value="2fa_resend">
                            <button type="submit" class="btn btn-link">Resend Code</button>
                        </form>
                        <div class="small text-muted mt-2">
                            Code expires in <span id="selfhelp-2fa-timer" data-time-remaining="<?php echo $code_remaining_time ?>">5:00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
