<?php

/**
 * @author Moheb, September 22nd, 2020.
 * 
 * AccountStatus defines all valid types of user statuses that are used for the status column
 * of the users table to determine the status of a user during the registration/login process.
 * 
 * Notes:
 * 
 * 1. In order to maintain the old status of a user in case the user gets blocked or
 *    requests a password reset for instance, the new status is appended to the old
 *    status. Statuses may stack to indicate multiple layers of statuses.
 * 
 *    Multiple status layers are delimited by the Delimeter constant as defined in this class's
 *    private field. Status layers are of the form X1::X2::...::Xn, where Xi, 1 <= i <= n and n >= 1,
 *    denotes a well-defined status from this class. A layer is removed/appended according to the
 *    logic of login/registration upon a user's particular interaction with the login/registration
 *    features.
 * 
 *    Example(s):
 *    i. A user has a current status of Active. The user forgets their password and issues a password
 *       reset request via email, then the user's status is set to Active::Reset_password.
 *       
 *       If the user resets their password successfully, their status is retrieved back to Active.
 *       
 *       If the user attempts to login without resetting their password through their issued email,
 *       then the user reaches the maximum login attempts, a blocked layer will be appended to the
 *       user's status (i.e Active::Reset_password::Blocked).
 * 
 *       If the block is lifted from the user's account, the status layer would reset back to 
 *       Active::Reset_password if the user has not reset their password through their issued email.
 * 
 *       If the user is blocked and successfully resets their password through their issued email,
 *       The block is lifted, and the account status is retrived back to Active.
 * 
 * 2. There are inadmissible cases where the login/registration logic does not check out, such as an
 *    account with a Rejected or Deleted status having a Reset_password status layer.
 *    
 */
#[AllowDynamicProperties]
class AccountStatus {
    const Active = 'Active';
    const Deleted = 'Deleted';
    const Blocked = 'Blocked';
    const LoginLayer = 'Login_layer';
    const RegistrationPending = 'Registration_pending';
    const ResetPassword = 'Reset_password';
    const Rejected = 'Rejected';

    private const Delimeter = '::::';

    /**
     * Appends a new status to an old status then returns the new full status layers 
     * indicating a new status layer has been added for a user to be accounted for in the
     * login/registration logic.
     * 
     * @param string $oldStatus
     * @param string $appendedStatus
     * @return string
     */
    static function appendStatus($oldStatus, $appendedStatus) {
        return $oldStatus . AccountStatus::Delimeter . $appendedStatus;
    }

    /**
     * Returns true whether a status layer specified by $currStatus has a $status; otherwise,
     * returns false.
     * 
     * @param string $currStatus
     * @param string $status
     * @return bool
     */
    static function hasStatus($currStatus, $status) {
        return (strpos($currStatus, $status) !== false); 
    }

    /**
     * Removes the $status from the status layers specified by $currStatus and returns the full
     * new status layers.
     * 
     * @param string $currStatus
     * @param string $status
     * @return string
     */
    static function removeStatus($currStatus, $status) {
        return str_replace(AccountStatus::Delimeter . $status, '', $currStatus);
    }
    
    /**
     * Retrieves the original status of a user whose current status is specified by $status,
     * removing all layers added to the user's initial status then returns the initial status.
     * 
     * @param string $status
     * @return string
     */
    static function retrieveOriginalStatus($status) {
        return strstr($status, AccountStatus::Delimeter, true);
    }
}