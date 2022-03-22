<?php
/**
 * Plugin Name: User Import
 * Plugin URI: 
 * Description: Plugin used to import users from CSV file
 * Version: 1.0
 * Author: Ankit Joshi
 * Author URI:
 */

// Add User Import Menu in Wp Admin
add_action('admin_menu', 'import_user_menu');
function import_user_menu(){
	add_menu_page( 'User Import', 'User Import', 'manage_options', 'user-import-csv', 'user_import_csv' );
}

// Function to show to option to import csv file
function user_import_csv(){  ?>
	<h1>Upload Users from CSV File</h1>
	<form  method="post" enctype="multipart/form-data">
		<input type='file' id='user_csv_file' name='user_csv_file' accept="csv"></input>
		<?php submit_button('Upload') ?>
	</form>
<?php
	import_handle_post();
}

// Function to handle data
function import_handle_post(){
    if(isset($_FILES['user_csv_file'])){

    	$fileName = $_FILES["user_csv_file"]["name"];
    	$allowed =  array('csv');
		$ext = pathinfo($fileName, PATHINFO_EXTENSION);
		if(!in_array($ext,$allowed) ) {
		    echo "<h2>File Not vaild. Please Upload CSV File only.</h2>";
		} else {
			$fileTempName = $_FILES["user_csv_file"]["tmp_name"];
		    if ($_FILES["user_csv_file"]["size"] > 0) {
		        $file = fopen($fileTempName, "r");
		        $count = 0;  ?>
		        <table class="widefat fixed" cellspacing="0">
    				<thead>
			        <tr>
				   		<th id="columnname" class="manage-column column-columnname" scope="col">Username</th>
				   		<th id="columnname" class="manage-column column-columnname" scope="col">Email</th>
				   		<th id="columnname" class="manage-column column-columnname" scope="col">Status</th>
				 	</tr>
				 	</thead>
			        <?php
			        while (($column = fgetcsv($file, ",")) !== FALSE) {
			        	$count++;
	    				if ($count == 1) { continue; }
							$username = $column[0];		
							$user_id = username_exists( $username );
							if ( !$user_id and email_exists($column[1]) == false ) {
								$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
								$user_id = wp_create_user( $username, $random_password, $column[1] );
								if($user_id){
									$user = new WP_User( $user_id );
									$user->set_role( 'subscriber' );
									update_user_meta($user_id,'first_name',$column[2]);
									update_user_meta($user_id,'last_name',$column[3]);
									$today = date("d/m/Y");
									$list = array
									(
										'Username'  => $column[0],
   										'Password'  => $random_password,
   										'Date' => $today			
									);

								} ?>
								<tr>
									<td class="column-columnname"><?php  echo $column[0]; ?></td>
									<td class="column-columnname"><?php echo $column[1]; ?></td>
									<td class="column-columnname">Success</td>
								</tr>
						<?php } else {
								$random_password = __('User already exists.  Password inherited.'); ?>
								<tr>
									<td class="column-columnname"><?php echo $column[0]; ?></td>
									<td class="column-columnname"><?php echo $column[1]; ?></td>
									<td class="column-columnname">User Alreay Exits</td>
								</tr>
							<?php }
		        	} ?>
		        </table>
		        <?php
		    }
		}
    }
}
