<?php /*
	Copyright 2014 Cédric Levieux, Jérémy Collot, ArmagNet

	This file is part of OpenTweetBar.

    OpenTweetBar is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    OpenTweetBar is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with OpenTweetBar.  If not, see <http://www.gnu.org/licenses/>.
*/
session_start();

$path = "../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

include_once("config/database.php");
require_once("engine/utils/SessionUtils.php");
require_once("engine/bo/AgendaBo.php");
require_once("engine/bo/MeetingBo.php");
require_once("engine/bo/ConclusionBo.php");

$connection = openConnection();

$agendaBo = AgendaBo::newInstance($connection);
$meetingBo = MeetingBo::newInstance($connection);
$conclusionBo = ConclusionBo::newInstance($connection);

$meeting = $meetingBo->getById($_REQUEST["meetingId"]);

if (!$meeting) {
	echo json_encode(array("ko" => "ko", "message" => "meeting_does_not_exist"));
	exit();
}

// TODO Compute the key // Verify the key

if (false) {
	echo json_encode(array("ko" => "ko", "message" => "meeting_not_accessible"));
	exit();
}


$agenda = $agendaBo->getById($_REQUEST["pointId"]);

if (!$agenda || $agenda["age_meeting_id"] != $meeting[$meetingBo->ID_FIELD]) {
	echo json_encode(array("ko" => "ko", "message" => "agenda_point_not_accessible"));
	exit();
}

$conclusion = $conclusionBo->getById($_REQUEST["conclusionId"]);

if (!$conclusion || $conclusion["con_agenda_id"] != $agenda[$agendaBo->ID_FIELD]) {
	echo json_encode(array("ko" => "ko", "message" => "conclusion_not_accessible"));
	exit();
}

$conclusion = array($conclusionBo->ID_FIELD => $conclusion[$conclusionBo->ID_FIELD]);
$conclusion["con_deleted"] = 1;

$conclusionBo->save($conclusion);

$agenda["age_objects"] = json_decode($agenda["age_objects"]);
$newObjects = array();
foreach($agenda["age_objects"] as $index => $object) {
	if (!isset($object->conclusionId) || $object->conclusionId != $conclusion[$conclusionBo->ID_FIELD]) {
//		unset($agenda["age_objects"][$index]);
//		break;
		$newObjects[] = $object;
	}
}

$agenda["age_objects"] = json_encode($newObjects);

$agendaBo->save($agenda);

$data["ok"] = "ok";

echo json_encode($data, JSON_NUMERIC_CHECK);
?>