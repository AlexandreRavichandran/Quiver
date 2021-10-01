
## Question

|Champ|Type|
|-|-|
|id|INT| 
|question|STRING| 
|created_at|DATETIME|
|updated_at|DATETIME|
|author|ENTITY [USER]| 

## Answer

|Champ|Type|
|-|-|
|id|INT|
|answer|TEXT|
|views_number|INT|
|created_at|DATETIME|
|updated_at|DATETIME|
|author|ENTITY [USER]|
|question|ENTITY [QUESTION]|

## User

|Champ|Type|
|-|-|
|id|INT|
|pseudonym|STRING|
|roles|STRING|
|email|STRING|
|password|STRING|
|firstName|STRING| 
|lastName|STRING| 
|description|TEXT| 
|qualification|TEXT| 
|created_at|DATETIME| 
|updated_at|DATETIME| 


## Space

|Champ|Type|
|-|-|
|id|INT| 
|name|STRING|
|description|TEXT|

## Comment

|Champ|Type|
|-|-|
|id|INT| 
|comment|TEXT| 
|created_at|DATETIME|
|updated_at|DATETIME|
|author|ENTITY [USER]| 
|answer|ENTITY [ANSWER]|

## SubComment

|Champ|Type|
|-|-|
|id|INT| 
|subcomment|TEXT|
|created_at|DATETIME|
|updated_at|DATETIME|
|author|ENTITY [USER]| 
|comment|ENTITY [COMMENT]|

