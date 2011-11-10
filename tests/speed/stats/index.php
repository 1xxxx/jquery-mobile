<?php
  $db = new PDO('sqlite:./stats.db');
  $db->query('CREATE TABLE IF NOT EXISTS stats (id INTEGER, agent TEXT, agent_version Text, agent_full TEXT, point TEXT, value REAL, time TIMESTAMP, pathname TEXT, PRIMARY KEY (id))');

  // making a sad attempt here to provide a clean REST-respecting url scheme.
  // stats with a GET returns - wait for it - the stats, and a post with the
  // the right params will create a new entry
  if ( $_SERVER['REQUEST_METHOD'] == "GET" ) {
     $agent = (empty($_GET['agent'])) ? '' : $_GET['agent'];
     $data_point = (empty($_GET['data_point'])) ? '' : $_GET['data_point'];

     $st = $db->prepare( "SELECT agent, agent_version, point, avg(value) as avg_value, pathname, strftime('%Y-%m-%d', time) as day FROM stats WHERE (agent_full like '%Mobile%' or agent_full like '%mobile%') and agent like :agent and point like :data_point GROUP BY agent, agent_version, pathname, point, strftime('%Y-%m-%d', time) ORDER BY time;");

     $st->execute(array(
        ':agent' => '%' . $agent . '%',
        ':data_point' => '%' . $data_point . '%'
     ));

     $result = $st->fetchAll(PDO::FETCH_ASSOC);
     header("Content-Type: application/json");
     echo json_encode($result);
  }  elseif ( $_POST['datapoint'] && $_POST['value'] && $_POST['agent'] && $_POST['pathname'] && $_POST['agentVersion']) {
     $st = $db->prepare('INSERT INTO stats (agent, agent_full, agent_version, point, value, pathname, time) VALUES (:agent, :agent_full, :agent_version, :data_point, :value, :pathname, DATETIME(\'now\'))');
     $st->execute(array(
            ':agent' => $_POST['agent'],
						':agent_full' => $_POST['agentFull'],
            ':agent_version' => $_POST['agentVersion'],
            ':data_point' => $_POST['datapoint'],
            ':value' => $_POST['value'],
            ':pathname' => $_POST['pathname']
        ));

     echo "success";
  }
?>