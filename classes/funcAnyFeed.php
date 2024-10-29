<?php
namespace anyfeedretriever;
function fetch_remote_file($url, $post_data=array())
{
    $post_body = '';
    if(!empty($post_data))
    {
        foreach($post_data as $key => $val)
        {
            $post_body .= '&'.urlencode($key).'='.urlencode($val);
        }
        $post_body = ltrim($post_body, '&');
    }

    try{

        $response = wp_remote_get( $url,$post_body );
        return wp_remote_retrieve_body( $response );
    }
    catch (\Exception $e){

        try{
            if(function_exists("curl_init"))
            {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                if(!empty($post_body))
                {
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body);
                }
                $data = curl_exec($ch);
                curl_close($ch);
                return $data;
            }
            else if(function_exists("fsockopen"))
            {
                $url = @parse_url($url);
                if(!$url['host'])
                {
                    return false;
                }
                if(!$url['port'])
                {
                    $url['port'] = 80;
                }
                if(!$url['path'])
                {
                    $url['path'] = "/";
                }
                if($url['query'])
                {
                    $url['path'] .= "?{$url['query']}";
                }
                $scheme = '';
                if($url['scheme'] == 'https')
                {
                    $scheme = 'ssl://';
                    if($url['port'] == 80)
                    {
                        $url['port'] = 443;
                    }
                }
                $fp = @fsockopen($scheme.$url['host'], $url['port'], $error_no, $error, 10);
                @stream_set_timeout($fp, 10);
                if(!$fp)
                {
                    return false;
                }
                $headers = array();
                if(!empty($post_body))
                {
                    $headers[] = "POST {$url['path']} HTTP/1.0";
                    $headers[] = "Content-Length: ".strlen($post_body);
                    $headers[] = "Content-Type: application/x-www-form-urlencoded";
                }
                else
                {
                    $headers[] = "GET {$url['path']} HTTP/1.0";
                }
                $headers[] = "Host: {$url['host']}";
                $headers[] = "Connection: Close";
                $headers[] = '';
                if(!empty($post_body))
                {
                    $headers[] = $post_body;
                }
                else
                {
                    // If we have no post body, we need to add an empty element to make sure we've got \r\n\r\n before the (non-existent) body starts
                    $headers[] = '';
                }
                $headers = implode("\r\n", $headers);
                if(!@fwrite($fp, $headers))
                {
                    return false;
                }
                while(!feof($fp))
                {
                    $data .= fgets($fp, 12800);
                }
                fclose($fp);
                $data = explode("\r\n\r\n", $data, 2);
                return $data[1];
            }
            else if(empty($post_data))
            {
                return @implode("", @file($url));
            }
            else
            {
                return false;
            }
        }
        catch (\Exception $ee){
            return $ee;
        }

    }
}