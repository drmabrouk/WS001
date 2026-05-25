<?php

namespace WSHC\Authentication;

/**
 * Handle OTP generation, storage, and validation.
 */
class OTPService {
    /**
     * Generate a new OTP and save its hash.
     *
     * @param int $user_id
     * @param int $expiry_minutes
     * @return string The raw OTP code.
     */
    public static function generate_otp($user_id, $expiry_minutes = 15) {
        global $wpdb;
        $otp = sprintf("%06d", mt_rand(0, 999999));
        $hash = password_hash($otp, PASSWORD_DEFAULT);
        $expiry = gmdate('Y-m-d H:i:s', time() + ($expiry_minutes * 60));

        $table = $wpdb->prefix . 'wshc_otps';
        
        // Remove old OTPs for this user
        $wpdb->delete($table, ['user_id' => $user_id]);

        $wpdb->insert($table, [
            'user_id'    => $user_id,
            'otp_hash'   => $hash,
            'expires_at' => $expiry,
        ]);

        return $otp;
    }

    /**
     * Validate an OTP for a given user.
     *
     * @param int    $user_id
     * @param string $otp
     * @return bool
     */
    public static function validate_otp($user_id, $otp) {
        global $wpdb;
        $table = $wpdb->prefix . 'wshc_otps';

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT otp_hash, expires_at FROM $table WHERE user_id = %d",
            $user_id
        ));

        if (!$row) {
            return false;
        }

        if (strtotime($row->expires_at) < time()) {
            $wpdb->delete($table, ['user_id' => $user_id]);
            return false;
        }

        if (password_verify($otp, $row->otp_hash)) {
            $wpdb->delete($table, ['user_id' => $user_id]);
            return true;
        }

        return false;
    }
}
