# jQuery-File-Upload-Qiniu
使server端适配了七牛，可以放在SAE了

详细教程请转至 
http://blog.juxianbd.com/86.html

##配合jQuery-File-Upload使用
只是php服务端代码，填上配置就可以用了
https://github.com/blueimp/jQuery-File-Upload

##配置
在qiniu.php文件中配置

```javascipt
$Conf = array(
        'AK' =>'AK', // AccessKey
        'SK' =>'SK',  ///SecretKey
        'bucket' => 'nodeupload',//空间名称
        'bucketUrl' => '空间域名', 
        'server'=>'http://localhost/ngupload/index.php?file=',//服务端 index.php位置，用来做删除七牛操作的；
        );
```
