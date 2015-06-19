<?php

/**
 * Status -
 * 0 - Pending
 * 1 - Active
 * 2 - Closed
 * 3 - Blocked
 * */
class erLhcoreClassChatCommand
{

    private static $supportedCommands = array(
        '!name' => 'self::setName',
        '!email' => 'self::setEmail',
        '!phone' => 'self::setPhone',
        '!goto' => 'self::redirectTo',
        '!translate' => 'self::startTranslation',
        '!screenshot' => 'self::takeScreenshot',
        '!contactform' => 'self::contactForm',
        '!block' => 'self::blockUser',
        '!close' => 'self::closeChat',
        '!delete' => 'self::deleteChat'
    );

    private static function extractCommand($message)
    {
        $params = explode(' ', $message);
        
        $commandData['command'] = array_shift($params);
        $commandData['argument'] = trim(implode(' ', $params));
        
        return $commandData;
    }

    /**
     * Processes command
     */
    public static function processCommand($params)
    {
        $commandData = self::extractCommand($params['msg']);
        
        if (key_exists($commandData['command'], self::$supportedCommands)) {
            $params['argument'] = $commandData['argument'];
            return call_user_func_array(self::$supportedCommands[$commandData['command']], array(
                $params
            ));
        }
        
        return false;
    }

    /**
     * Updates chat nick.
     *
     * @param array $params            
     *
     * @return boolean
     */
    public static function setName($params)
    {
        
        // Update object attribute
        $params['chat']->nick = $params['argument'];
        
        // Update only
        $db = ezcDbInstance::get();
        $stmt = $db->prepare('UPDATE lh_chat SET nick = :nick WHERE id = :id');
        $stmt->bindValue(':id', $params['chat']->id, PDO::PARAM_INT);
        $stmt->bindValue(':nick', $params['chat']->nick, PDO::PARAM_STR);
        $stmt->execute();
        
        return true;
    }

    /**
     * Updates chat email.
     *
     * @param array $params            
     *
     * @return boolean
     */
    public static function setEmail($params)
    {
        
        // Update object attribute
        $params['chat']->email = $params['argument'];
        
        // Schedule interface update
        $params['chat']->operation_admin .= "lhinst.updateVoteStatus(" . $params['chat']->id . ");";
        
        // Update only
        $db = ezcDbInstance::get();
        $stmt = $db->prepare('UPDATE lh_chat SET email = :email, operation_admin = :operation_admin WHERE id = :id');
        $stmt->bindValue(':id', $params['chat']->id, PDO::PARAM_INT);
        $stmt->bindValue(':email', $params['chat']->email, PDO::PARAM_STR);
        $stmt->bindValue(':operation_admin', $params['chat']->operation_admin, PDO::PARAM_STR);
        $stmt->execute();
        
        return true;
    }

    /**
     * Updates chat phone
     *
     * @param array $params            
     *
     * @return boolean
     */
    public static function setPhone($params)
    {
        
        // Update object attribute
        $params['chat']->phone = $params['argument'];
        
        // Schedule interface update
        $params['chat']->operation_admin .= "lhinst.updateVoteStatus(" . $params['chat']->id . ");";
        
        // Update only
        $db = ezcDbInstance::get();
        $stmt = $db->prepare('UPDATE lh_chat SET phone = :phone, operation_admin = :operation_admin WHERE id = :id');
        $stmt->bindValue(':id', $params['chat']->id, PDO::PARAM_INT);
        $stmt->bindValue(':phone', $params['chat']->phone, PDO::PARAM_STR);
        $stmt->bindValue(':operation_admin', $params['chat']->operation_admin, PDO::PARAM_STR);
        $stmt->execute();
        
        return true;
    }

    /**
     * Redirects user to specified URL
     *
     * @param array $params            
     *
     * @return boolean
     */
    public static function redirectTo($params)
    {
        
        // Update object attribute
        $params['chat']->operation .= 'lhc_chat_redirect:' . str_replace(':', '__SPLIT__', $params['argument']) . "\n";
        
        // Update only
        $db = ezcDbInstance::get();
        $stmt = $db->prepare('UPDATE lh_chat SET operation = :operation WHERE id = :id');
        $stmt->bindValue(':id', $params['chat']->id, PDO::PARAM_INT);
        $stmt->bindValue(':operation', $params['chat']->operation, PDO::PARAM_STR);
        $stmt->execute();
        
        return true;
    }

   
    
    public static function startTranslation($params)
    {
        // Schedule interface update
        $params['chat']->operation_admin .= "lhc.methodCall('lhc.translation','startTranslation',{'btn':$('#start-trans-btn-{$params['chat']->id}'),'chat_id':'{$params['chat']->id}'});";
        
        // Update only
        $db = ezcDbInstance::get();
        $stmt = $db->prepare('UPDATE lh_chat SET operation_admin = :operation_admin WHERE id = :id');
        $stmt->bindValue(':id', $params['chat']->id, PDO::PARAM_INT);
        $stmt->bindValue(':operation_admin', $params['chat']->operation_admin, PDO::PARAM_STR);
        $stmt->execute();
        
        return true;
    }
    
    public static function takeScreenshot($params)
    {
        // Update object attribute
        $params['chat']->operation .= "lhc_screenshot\n";
        
        // Update only
        $db = ezcDbInstance::get();
        $stmt = $db->prepare('UPDATE lh_chat SET operation = :operation WHERE id = :id');
        $stmt->bindValue(':id', $params['chat']->id, PDO::PARAM_INT);
        $stmt->bindValue(':operation', $params['chat']->operation, PDO::PARAM_STR);
        $stmt->execute();
        
        return true;
    }
    
    public static function contactForm($params)
    {
        // Schedule interface update
        $params['chat']->operation_admin .= "lhinst.redirectContact('{$params['chat']->id}');";
        
        // Update only
        $db = ezcDbInstance::get();
        $stmt = $db->prepare('UPDATE lh_chat SET operation_admin = :operation_admin WHERE id = :id');
        $stmt->bindValue(':id', $params['chat']->id, PDO::PARAM_INT);
        $stmt->bindValue(':operation_admin', $params['chat']->operation_admin, PDO::PARAM_STR);
        $stmt->execute();
        
        return true;        
    }
    
    public static function blockUser($params)
    {
        // Schedule interface update
        $params['chat']->operation_admin .= "lhinst.blockUser('{$params['chat']->id}');";
        
        // Update only
        $db = ezcDbInstance::get();
        $stmt = $db->prepare('UPDATE lh_chat SET operation_admin = :operation_admin WHERE id = :id');
        $stmt->bindValue(':id', $params['chat']->id, PDO::PARAM_INT);
        $stmt->bindValue(':operation_admin', $params['chat']->operation_admin, PDO::PARAM_STR);
        $stmt->execute();
        
        return true;        
    }
    
    public static function closeChat($params)
    {
        // Schedule interface update
        $params['chat']->operation_admin .= "lhinst.closeActiveChatDialog('{$params['chat']->id}',$('#tabs'),true);";
        
        // Update only
        $db = ezcDbInstance::get();
        $stmt = $db->prepare('UPDATE lh_chat SET operation_admin = :operation_admin WHERE id = :id');
        $stmt->bindValue(':id', $params['chat']->id, PDO::PARAM_INT);
        $stmt->bindValue(':operation_admin', $params['chat']->operation_admin, PDO::PARAM_STR);
        $stmt->execute();
        
        return true;        
    }
    
    public static function deleteChat($params)
    {
        // Schedule interface update
        $params['chat']->operation_admin .= "lhinst.deleteChat('{$params['chat']->id}',$('#tabs'),true);";
                
        // Update only
        $db = ezcDbInstance::get();
        $stmt = $db->prepare('UPDATE lh_chat SET operation_admin = :operation_admin WHERE id = :id');
        $stmt->bindValue(':id', $params['chat']->id, PDO::PARAM_INT);
        $stmt->bindValue(':operation_admin', $params['chat']->operation_admin, PDO::PARAM_STR);
        $stmt->execute();
        
        return true;        
    }
}

?>