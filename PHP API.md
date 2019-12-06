# Excel问答系统PHP接口说明文档

##### 接口列表：

- [1.查询问题](#search)
- [2.添加问题](#addquestion)
- [3.修改问题](#editquestion)
- [4.删除问题](#delquestion)
- [5.回答者登入](#answerlogin)
- [6.回答者登出](#answerlogout)
- [7.检查回答者是否登入](#answerchecklogin)
- [8.新增回答者](#addanswer)

---
<span id = "search"></span>

##### **1\. 查询问题**

###### 接口功能
> 根据查询条件查询数据库，返回匹配查询条件的记录。

###### URL
> /search.php

###### 返回格式
> JSON

###### HTTP请求方式
> GET

###### 请求参数
> |参数|必选|类型|缺省值|说明|
> |:-----  |:-------|:-----|:----                               |:-----  |
> |type    |false    |string|“excel”                          |问题类型（“excel”或“vba”）                          |
> |keyword    |false    |string   |“”|查询关键字（问题id/问题文件名/问题描述）|
> |start |false |int |0|本次查询返回记录的开始编号|
> |count |false |int |10|本次查询总共返回几条记录|

###### 返回字段
> |返回字段|字段类型|说明                              |
> |:-----   |:------|:-----------------------------   |
> |ecode   |int    |返回结果状态。0：成功；小于0：出错。   |
> |errorMsg  |string | 出错信息                  |
> |timestamp |string |时间戳                         |
> |records |object[] |问题对象数组 |
> |start |int |本次查询返回记录的开始编号 |
> |count |int |本次查询总共返回几条记录 |
> |totalcount |int |匹配查询条件的总记录数 |

###### 问题对象字段
> |返回字段|字段类型|说明                              |
> |:-----   |:------|:-----------------------------   |
> |id   |string    |问题id   |
> |question_desc  |string | 问题描述              |
> |update_time |string |问题更新时间                         |
> |question_file_name |string |问题文件的文件名 |
> |question_file_url |string |问题文件的下载url |
> |answer_file_name |string |回答文件的文件名 |
> |answer_file_url |string |回答文件的下载url |
> |answerer |string |回答者 |

###### 接口示例
> 请求：/search.php?type="excel"&keyword="排序"&start=0&count=10
>
> 返回：
``` json
{
    "ecode":0,
    "errorMsg":"",
    "timestamp":"2019-12-05 10:00:09",
    "records":[
        {
            "id":"6",
            "question_desc":"怎么使用excel排序",
            "update_time":"2019-12-04 18:02:04",
            "question_file_name":"question.png",
            "question_file_url":"question_file/6ff6bdf371f27ebe46a88ca055caa337.png",
            "answer_file_name":"anwser.txt",
            "answer_file_url":"./answer_file/e1937f4aa102d3b7f68ec0a6813b406b.txt",
            "answerer":"柯南"
        }
    ],
    "start":"0",
    "count":"10",
    "totalcount":1
}
```

---
<span id = "addquestion"></span>

##### **2\. 添加问题**

###### 接口功能
> 添加一条新的问题。

###### URL
> /addquestion.php

###### 返回格式
> JSON

###### HTTP请求方式
> POST

###### 请求参数
> |参数|必选|类型|缺省值|说明|
> |:-----  |:-------|:-----|:----                               |:-----  |
> |type    |true    |string|                          |问题类型（“excel”或“vba”）                          |
> |desc    |true    |string   ||问题描述|
> |email |false |string |“”|问题者email|
> |phone |false |string |“”|问题者手机号|
> |file |false |file object |null|问题文件（问题相关的需要上传的文件）|

###### 返回字段
> |返回字段|字段类型|说明                              |
> |:-----   |:------|:-----------------------------   |
> |ecode   |int    |返回结果状态。0：成功；小于0：出错。   |
> |errorMsg  |string | 出错信息                  |
> |timestamp |string |时间戳                         |

###### 接口示例
> 请求：/addquestion.php?type="excel"&desc="怎么使用excel排序"&email=“”&phone=“”
>
> 返回：
``` json
{
    "ecode":0,
    "errorMsg":"",
    "timestamp":"2019-12-05 10:00:09"
}
```

---
<span id = "editquestion"></span>

##### **3\. 修改问题**

###### 接口功能
> 修改一条问题。

###### URL
> /editquestion.php

###### 返回格式
> JSON

###### HTTP请求方式
> POST

###### 请求参数
> |参数|必选|类型|缺省值|说明|
> |:-----  |:-------|:-----|:----                               |:-----  |
> |id    |true    |string|                          |问题id                          |
> |notify    |false    |string   |“”|通知方式（“email”或“phone”）|
> |file |false |file object |null|问题文件（问题相关的需要上传的文件）|

###### 返回字段
> |返回字段|字段类型|说明                              |
> |:-----   |:------|:-----------------------------   |
> |ecode   |int    |返回结果状态。0：成功；小于0：出错。   |
> |errorMsg  |string | 出错信息                  |
> |timestamp |string |时间戳                         |

###### 接口示例
> 请求：/editquestion.php?id="3"&notify=""&email=“”&phone=“”
>
> 返回：
``` json
{
    "ecode":0,
    "errorMsg":"",
    "timestamp":"2019-12-05 10:00:09"
}
```
---
<span id = "delquestion"></span>

##### **4\. 删除问题**

###### 接口功能
> 删除一条问题。

###### URL
> /delquestion.php

###### 返回格式
> JSON

###### HTTP请求方式
> POST

###### 请求参数
> |参数|必选|类型|缺省值|说明|
> |:-----  |:-------|:-----|:----                               |:-----  |
> |id    |true    |string|                          |问题id                          |

###### 返回字段
> |返回字段|字段类型|说明                              |
> |:-----   |:------|:-----------------------------   |
> |ecode   |int    |返回结果状态。0：成功；小于0：出错。   |
> |errorMsg  |string | 出错信息                  |
> |timestamp |string |时间戳                         |

###### 接口示例
> 请求：/delquestion.php?id="3“
>
> 返回：
``` json
{
    "ecode":0,
    "errorMsg":"",
    "timestamp":"2019-12-05 10:00:09"
}
```
---
<span id = "answerlogin"></span>

##### **5\. 回答者登入**

###### 接口功能
> 回答者登入。

###### URL
> /answerlogin.php

###### 返回格式
> JSON

###### HTTP请求方式
> POST

###### 请求参数
> |参数|必选|类型|缺省值|说明|
> |:-----  |:-------|:-----|:----                               |:-----  |
> |name    |true    |string|                          |回答者用户名                          |
> |password |true |string| |回答者密码 |

###### 返回字段
> |返回字段|字段类型|说明                              |
> |:-----   |:------|:-----------------------------   |
> |ecode   |int    |返回结果状态。0：成功；小于0：出错。   |
> |errorMsg  |string | 出错信息                  |
> |timestamp |string |时间戳                         |
> |name |string |回答者用户名 |

###### 接口示例
> 请求：/answerlogin.php?name="柯南”&password=“123”
>
> 返回：
``` json
{
    "ecode":0,
    "errorMsg":"",
    "timestamp":"2019-12-05 10:00:09",
    "name":"柯南"
}
```
---
<span id = "answerlogout"></span>

##### **6\. 回答者登出**

###### 接口功能
> 回答者登出。

###### URL
> /answerlogout.php

###### 返回格式
> JSON

###### HTTP请求方式
> POST

###### 请求参数
> 无

###### 返回字段
> |返回字段|字段类型|说明                              |
> |:-----   |:------|:-----------------------------   |
> |ecode   |int    |返回结果状态。0：成功；小于0：出错。   |
> |errorMsg  |string | 出错信息                  |
> |timestamp |string |时间戳                         |

###### 接口示例
> 请求：/answerlogout.php
>
> 返回：
``` json
{
    "ecode":0,
    "errorMsg":"",
    "timestamp":"2019-12-05 10:00:09"
}
```

---
<span id = "answerchecklogin"></span>

##### **7\. 检查回答者是否登入**

###### 接口功能
> 检查回答者是否登入。

###### URL
> /answerchecklogin.php

###### 返回格式
> JSON

###### HTTP请求方式
> POST

###### 请求参数
> 无

###### 返回字段
> |返回字段|字段类型|说明                              |
> |:-----   |:------|:-----------------------------   |
> |ecode   |int    |返回结果状态。0：已登入；小于0：未登入。   |
> |errorMsg  |string | 出错信息                  |
> |timestamp |string |时间戳                         |
> |name |string |登入的用户名 |

###### 接口示例
> 请求：/answerchecklogin.php
>
> 返回：
``` json
{
    "ecode":0,
    "errorMsg":"",
    "timestamp":"2019-12-05 10:00:09",
    "name":"柯南"
}
```
---
<span id = "addanswer"></span>

##### **8\. 新增回答者**

###### 接口功能
> 新增回答者。

###### URL
> /addanswer.php

###### 返回格式
> JSON

###### HTTP请求方式
> POST

###### 请求参数
> |参数|必选|类型|缺省值|说明|
> |:-----  |:-------|:-----|:----                               |:-----  |
> |code|true|string||管理员码|
> |name    |true    |string|                          |新增回答者用户名                          |
> |password |true |string| |新增回答者密码 |

###### 返回字段
> |返回字段|字段类型|说明                              |
> |:-----   |:------|:-----------------------------   |
> |ecode   |int    |返回结果状态。0：成功；小于0：出错。   |
> |errorMsg  |string | 出错信息                  |
> |timestamp |string |时间戳                         |

###### 接口示例
> 请求：/addanswer.php?name="柯南”&password=“123”
>
> 返回：
``` json
{
    "ecode":0,
    "errorMsg":"",
    "timestamp":"2019-12-05 10:00:09"
}
```



