<?php
/**
 * Script to set up a test session for testing upload functionality
 * This simulates a logged-in user session
 */

echo "=== Setting up Test Session for testuser ===\n\n";

// Start session
session_start();

// Check if we're in the right environment
if (!defined('BASEPATH')) {
    echo "❌ This script must be run from within the CodeIgniter environment\n";
    exit;
}

try {
    // Load the database
    $this->load->database();
    
    // Get testuser info from database
    $query = $this->db->select('id, username, email, first_name, last_name')
                      ->from('users')
                      ->where('username', 'testuser')
                      ->get();
    
    if ($query->num_rows() > 0) {
        $user = $query->row_array();
        
        // Set up session data similar to user_login_success method
        $session_data = array(
            'email' => $user['email'],
            'name' => $user['first_name'] . ' ' . $user['last_name'],
            'account_type' => 'USER',
            'timestamp' => time(),
            'profile_image' => null,
            'id' => $user['id']
        );
        
        // Set session data
        $this->session->set_userdata('logged_in', $session_data);
        
        echo "✅ Test session created successfully!\n";
        echo "   User ID: " . $user['id'] . "\n";
        echo "   Username: " . $user['username'] . "\n";
        echo "   Email: " . $user['email'] . "\n";
        echo "   Session ID: " . session_id() . "\n";
        
        // Verify session was set
        $logged_in = $this->session->userdata('logged_in');
        if ($logged_in) {
            echo "   ✅ Session data verified: " . json_encode($logged_in) . "\n";
        } else {
            echo "   ❌ Session data not set properly\n";
        }
        
    } else {
        echo "❌ testuser not found in database\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error setting up test session: " . $e->getMessage() . "\n";
}

echo "\n=== Test Session Setup Complete ===\n";
?>
