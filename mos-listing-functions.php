<?php
function mos_xml_admin_enqueue_scripts(){
	global $pagenow, $typenow;
	//page=mos-csv-importer-export-options
	if ($pagenow == 'admin.php' AND $_GET['page'] == 'mos-listing-options') {
		wp_enqueue_style( 'bootstrap.min', plugins_url( 'css/bootstrap.min.css', __FILE__ ) );
		wp_enqueue_style( 'mos-listing', plugins_url( 'css/mos-listing.css', __FILE__ ) );

		wp_enqueue_media();
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'bootstrap.min', plugins_url( 'js/bootstrap.min.js', __FILE__ ), array('jquery') );
		wp_enqueue_script( 'mos-listing', plugins_url( 'js/mos-listing.js', __FILE__ ), array('jquery') );
		wp_localize_script('mos-listing',  'ajax_link', admin_url( 'admin-ajax.php' ));
	}
}
add_action( 'admin_enqueue_scripts', 'mos_xml_admin_enqueue_scripts' );

add_action( 'wp_ajax_mos_xml_upload','mos_xml_upload_callback' );
add_action( 'wp_ajax_nopriv_mos_xml_upload','mos_xml_upload_callback' );
function mos_xml_upload_callback(){
	$file = basename($_FILES["file"]["name"]);
	$target_dir = '../wp-content/uploads/xmlFiles';
	if(!is_dir($target_dir)) {
		mkdir($target_dir);
	}
	$target_file = $target_dir . '/' . $file;
	if (mos_check_file_ext(array('xml', 'XML'), $file)) {
		if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
	        //echo home_url( '/wp-content/uploads/xmlFiles/' ) . basename( $_FILES["file"]["name"]);
			$xmlstring = file_get_contents(home_url( '/wp-content/uploads/xmlFiles/' ) . basename( $_FILES["file"]["name"]));
			$xml = simplexml_load_string($xmlstring, "SimpleXMLElement", LIBXML_NOCDATA);
			$json = json_encode($xml);
			$array = json_decode($json,TRUE);
			foreach ($array["residential"] as $key => $value) {
				$title = ( $value["headline"]) ? $value["headline"] : 'Realty Property';
				$my_post = array(
					'post_title'    => wp_strip_all_tags( $title ),
					'post_content'  => $value["description"],
					'post_status'   => 'publish',
					'post_type'   => 'project',
				);
				$post_id = wp_insert_post( $my_post );
				$address = $value["address"]["street"] . ', ' . $value["address"]["suburb"] . ', ' . $value["address"]["state"] . '-' . $value["address"]["postcode"]  . ', ' . $value["address"]["country"];
				add_post_meta($post_id, 'search-map-location', $address, true);
				if (@$value["objects"]["img"]["0"]["@attributes"]["url"]) {
					generate_featured_image( $value["objects"]["img"]["0"]["@attributes"]["url"], $post_id  );
				}

				$n = 0;
				$data = array();
 				if (!@$value["listingAgent"]["name"]) {
					foreach ($value["listingAgent"] as $agent) {						
						add_post_meta($post_id, '_mosacademy_child_agent_name_'.$n, $agent["name"], true);						
						$data[$n]['_mosacademy_child_agent_name'] = $agent["name"];
						if ($agent["telephone"]) {
							$data[$n]['_mosacademy_child_agent_phone'][] = $agent["telephone"];
						}
						if ($agent["email"]) {
							$data[$n]['_mosacademy_child_agent_email'][] = $agent["email"];
						}
						$n++;

					}

				} else {					
					add_post_meta($post_id, '_mosacademy_child_agent_name_'.$n, $value["listingAgent"]["name"], true);	
					$data[$n]['_mosacademy_child_agent_name'] = $value["listingAgent"]["name"];					
					if ($value["listingAgent"]["telephone"]) {
						$data[$n]['_mosacademy_child_agent_phone'][] = $value["listingAgent"]["telephone"];
					}
					if ($value["listingAgent"]["email"]) {
						$data[$n]['_mosacademy_child_agent_email'][] = $value["listingAgent"]["email"];
					}
				}
				add_post_meta( $post_id, '_mosacademy_child_agent_Group', $data );

				if ($value["category"]["@attributes"]["name"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_property_type', $value["category"]["@attributes"]["name"], true);
				}
				if ($value["features"]["livingAreas"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_area', $value["features"]["livingAreas"], true);
				}
				if ($value["price"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_price', round($value["price"]), true);
				}
				if ($value["features"]["bedrooms"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_bed', $value["features"]["bedrooms"], true);
				}
				if ($value["features"]["bathrooms"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_toilets', $value["features"]["bathrooms"], true);
				}
				if ($value["features"]["garages"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_car_parking', $value["features"]["garages"], true);
				}
				if ($value["features"]["ensuite"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_ensuite', $value["features"]["ensuite"], true);
				}
				if ($value["features"]["remoteGarage"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_remoteGarage', $value["features"]["remoteGarage"], true);
				}
				if ($value["features"]["secureParking"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_secureParking', $value["features"]["secureParking"], true);
				}
				if ($value["features"]["airConditioning"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_airConditioning', $value["features"]["airConditioning"], true);
				}
				if ($value["features"]["alarmSystem"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_alarmSystem', $value["features"]["alarmSystem"], true);
				}
				if ($value["features"]["vacuumSystem"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_vacuumSystem', $value["features"]["vacuumSystem"], true);
				}
				if ($value["features"]["intercom"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_intercom', $value["features"]["intercom"], true);
				}
				if ($value["features"]["poolInGround"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_poolInGround', $value["features"]["poolInGround"], true);
				}
				if ($value["features"]["poolAboveGround"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_poolAboveGround', $value["features"]["poolAboveGround"], true);
				}
				if ($value["features"]["tennisCourt"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_tennisCourt', $value["features"]["tennisCourt"], true);
				}
				if ($value["features"]["balcony"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_balcony', $value["features"]["balcony"], true);
				}
				if ($value["features"]["deck"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_deck', $value["features"]["deck"], true);
				}
				if ($value["features"]["courtyard"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_courtyard', $value["features"]["courtyard"], true);
				}
				if ($value["features"]["outdoorEnt"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_outdoorEnt', $value["features"]["outdoorEnt"], true);
				}
				if ($value["features"]["shed"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_shed', $value["features"]["shed"], true);
				}
				if ($value["features"]["fullyFenced"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_fullyFenced', $value["features"]["fullyFenced"], true);
				}
				if ($value["features"]["openFirePlace"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_openFirePlace', $value["features"]["openFirePlace"], true);
				}
				if ($value["features"]["insideSpa"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_insideSpa', $value["features"]["insideSpa"], true);
				}
				if ($value["features"]["outsideSpa"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_outsideSpa', $value["features"]["outsideSpa"], true);
				}
				if ($value["features"]["broadband"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_broadband', $value["features"]["broadband"], true);
				}
				if ($value["features"]["builtInRobes"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_builtInRobes', $value["features"]["builtInRobes"], true);
				}
				if ($value["features"]["dishwasher"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_dishwasher', $value["features"]["dishwasher"], true);
				}
				if ($value["features"]["ductedCooling"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_ductedCooling', $value["features"]["ductedCooling"], true);
				}
				if ($value["features"]["ductedHeating"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_ductedHeating', $value["features"]["ductedHeating"], true);
				}
				if ($value["features"]["evaporativeCooling"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_evaporativeCooling', $value["features"]["evaporativeCooling"], true);
				}
				if ($value["features"]["floorboards"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_floorboards', $value["features"]["floorboards"], true);
				}
				if ($value["features"]["gasHeating"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_gasHeating', $value["features"]["gasHeating"], true);
				}
				if ($value["features"]["gym"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_gym', $value["features"]["gym"], true);
				}
				if ($value["features"]["hydronicHeating"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_hydronicHeating', $value["features"]["hydronicHeating"], true);
				}
				if ($value["features"]["payTV"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_payTV', $value["features"]["payTV"], true);
				}
				if ($value["features"]["reverseCycleAirCon"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_reverseCycleAirCon', $value["features"]["reverseCycleAirCon"], true);
				}
				if ($value["features"]["rumpusRoom"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_rumpusRoom', $value["features"]["rumpusRoom"], true);
				}
				if ($value["features"]["splitSystemAirCon"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_splitSystemAirCon', $value["features"]["splitSystemAirCon"], true);
				}
				if ($value["features"]["splitSystemHeating"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_splitSystemHeating', $value["features"]["splitSystemHeating"], true);
				}
				if ($value["features"]["study"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_study', $value["features"]["study"], true);
				}
				if ($value["features"]["workshop"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_workshop', $value["features"]["workshop"], true);
				}
				if ($value["features"]["otherFeatures"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_otherFeatures', $value["features"]["otherFeatures"], true);
				}
				if ($value["ecoFriendly"]["solarPanels"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_solarPanels', $value["ecoFriendly"]["solarPanels"], true);
				}
				if ($value["ecoFriendly"]["solarHotWater"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_solarHotWater', $value["ecoFriendly"]["solarHotWater"], true);
				}
				if ($value["ecoFriendly"]["waterTank"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_waterTank', $value["ecoFriendly"]["waterTank"], true);
				}
				if ($value["ecoFriendly"]["greyWaterSystem"]) {
					add_post_meta($post_id, '_mosacademy_child_project_key_greyWaterSystem', $value["ecoFriendly"]["greyWaterSystem"], true);
				}
				if ($value["agentID"]) {
					add_post_meta($post_id, '_mosacademy_child_project_agentID', $value["agentID"], true);
				}
				if ($value["uniqueID"]) {
					add_post_meta($post_id, '_mosacademy_child_project_uniqueID', $value["uniqueID"], true);
				}
				if ($value["objects"]["floorplan"]["0"]["@attributes"]["url"]) {
					$data = upload_image ( '$value["objects"]["floorplan"]["0"]["@attributes"]["url"]', $post_id );
					$attach_url = $data['image_url'];
					add_post_meta($post_id, '_mosacademy_child_project_plan', $attach_url, true);
				}
				
			}
	    } else {
	        echo "Sorry, there was an error uploading your file.";
	    }
	}
	/*header("Content-type: text/x-json");
	echo json_encode($data);
	//http://guardiangrouprealty.belocal.today/wp-admin/edit.php?post_type=project*/
	wp_redirect( admin_url( '/edit.php?post_type=project' ));
	die();
}

function mos_check_file_ext ($allowed, $filename) {
	if (!is_array($allowed)) $allowed =  array($allowed);
	$ext = pathinfo($filename, PATHINFO_EXTENSION);
	if(!in_array($ext,$allowed) ) {
	    return false;
	}
	return true;
}
function upload_image ( $image_url, $post_id = 0 ) {
	$output = array();	
    $upload_dir = wp_upload_dir();
    $image_data = file_get_contents($image_url);
    $filename = basename($image_url);
    if(wp_mkdir_p($upload_dir['path']))     $file = $upload_dir['path'] . '/' . $filename;
    else                                    $file = $upload_dir['basedir'] . '/' . $filename;
    file_put_contents($file, $image_data);

    $wp_filetype = wp_check_filetype($filename, null );
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => sanitize_file_name($filename),
        'post_content' => '',
        'post_status' => 'inherit'
    );
    $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
    wp_update_attachment_metadata( $attach_id, $attach_data );
    $output['attach_id'] = $attach_id;
    $output['image_url'] = $file;
    return $output;
}
function generate_featured_image( $image_url, $post_id  ) {
	$data = upload_image ( $image_url, $post_id );
	$attach_id = $data['attach_id'];
    $res2= set_post_thumbnail( $post_id, $attach_id );
}