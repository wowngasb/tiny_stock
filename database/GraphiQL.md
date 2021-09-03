# GraphQL Test

+ Run App

``` shell
python app.py
```

+ Test URL [http://127.0.0.1:8080/graphql?query=](http://127.0.0.1:8080/graphql?query=)

## Base Field

```
{
  hello helloABC:hello(name:"ABC") deprecatedField 
}
```

```
{
  "data": {
    "hello": "Hello, world!",
    "helloABC": "Hello, ABC!",
    "deprecatedField": "You can request deprecated field, but it is not displayed in auto-generated documentation by default."
  }
}
```

## Exception Field

```
{
    hello fieldWithException
}
```

```
{
  "errors": [
    {
      "message": "Exception message thrown in field resolver",
      "locations": [
        {
          "column": 11,
          "line": 2
        }
      ]
    }
  ],
  "data": {
    "hello": "Hello, world!",
    "fieldWithException": null
  }
}
```


## Room Base Query

```
query TestBaseRoomQuery($room_id: ID!){
  room(room_id: $room_id){
    room_id room_title room_status 
  }
}
```

```
{
  "room_id": 101
}
```

```
{
  "data": {
    "room": {
      "room_id": "101",
      "room_title": "live test 101",
      "room_status": "normal"
    }
  }
}
```

## Room Dms Query

```
query TestDmsRoomQuery($room_id: ID!){
  room(room_id: $room_id){
    room_id 
    dms_sub_key dms_pub_key lss_app stream
    chat_topic present_topic sys_notify_lss_topic sync_room_topic sync_user_topic
    currentUser{
      user{
        user_id nick avatar user_type
      }
      user_agent client_id
    }
  }
}
```

```
{
  "room_id": 101
}
```

```
{
  "data": {
    "room": {
      "room_id": "101",
      "dms_sub_key": "sub_eae37e48dab5f305516d07788eaaea60",
      "dms_pub_key": "pub_5bfb7a0ced7adb2ce454575747762679",
      "lss_app": "dyy_1736_133",
      "stream": "a0c3d2dd3b4688f31da13991477980d9",
      "chat_topic": "room_101",
      "present_topic": "__present__room_101",
      "sys_notify_lss_topic": "sys/notify/lss",
      "sync_room_topic": "sync_room",
      "sync_user_topic": "sync_user",
      "currentUser": {
        "user": {
          "user_id": "10000",
          "nick": "Nick10000",
          "avatar": "http://58jinrongyun.com/dist/dyy/view/jiaoyu/mobile/images/male.png",
          "user_type": "authorized"
        },
        "user_agent": "WEB",
        "client_id": "1496991160_WEB452_10000"
      }
    }
  }
}
```

## Room Player query

```
query TestBaseRoomQuery($room_id: ID!, $player_type_mps: String!, $player_type_ad: String!){
  room(room_id: $room_id){
    room_id
    mpsPlayerConfig: playerConfig(player_type: $player_type_mps){
			...fragmentPlayer
    }
    aodianPlayerConfig: playerConfig(player_type: $player_type_ad){
      ...fragmentPlayer
    }
  }
}
fragment fragmentPlayer on PlayerConfigUnion{
    ... on PlayerAodianConfig{
      player_type rtmpUrl hlsUrl autostart bufferlength maxbufferlength stretching controlbardisplay defvolume adveDeAddr
    }
    ... on PlayerMpsConfig{
      player_type player_type uin appId autostart stretching mobilefullscreen controlbardisplay isclickplay isfullscreen 
    }
}

```

```
{
  "room_id": 101,
  "player_type_ad": "aodianplayer",
  "player_type_mps": "mps"
}
```

```
{
  "data": {
    "room": {
      "room_id": "101",
      "mpsPlayerConfig": {
        "player_type": "mpsplayer",
        "uin": 13830,
        "appId": "fHNNBuuB3BbUWJiP",
        "autostart": 1,
        "stretching": 1,
        "mobilefullscreen": 0,
        "controlbardisplay": "enable",
        "isclickplay": 1,
        "isfullscreen": 1
      },
      "aodianPlayerConfig": {
        "player_type": "aodianplayer",
        "rtmpUrl": "rtmp://13830.lssplay.aodianyun.com/dyy_1736_133/a0c3d2dd3b4688f31da13991477980d9",
        "hlsUrl": "http://13830.hlsplay.aodianyun.com/dyy_1736_133/a0c3d2dd3b4688f31da13991477980d9.m3u8",
        "autostart": 1,
        "bufferlength": 1,
        "maxbufferlength": 1,
        "stretching": 1,
        "controlbardisplay": "enable",
        "defvolume": 80,
        "adveDeAddr": "http://static.douyalive.com/aae/dyy/assets/img/play_bj.png"
      }
    }
  }
}
```

## User Base Query

```
query TestUserQuery($room_id: ID!, $user_id: ID!, $user_id_mgr: ID!){
  user(room_id: $room_id, user_id: $user_id){
    user_id nick avatar user_type
  }
  user_mgr: user(room_id: $room_id, user_id: $user_id_mgr){
    user_id nick avatar user_type
  }
}
```

```
{
  "room_id": 101,
  "user_id": 10001,
  "user_id_mgr": 1000
}
```

```
{
  "data": {
    "user": {
      "user_id": "10001",
      "nick": "Nick10001",
      "avatar": "http://58jinrongyun.com/dist/dyy/view/jiaoyu/mobile/images/male.png",
      "user_type": "authorized"
    },
    "user_mgr": {
      "user_id": "1000",
      "nick": "Mgr1000",
      "avatar": "http://58jinrongyun.com/dist/dyy/view/jiaoyu/mobile/images/male.png",
      "user_type": "manager"
    }
  }
}
```

## Msg Base Query

```
query TestMsgQuery($msg_id: ID!, $msg_id_2: ID!, $msg_id_3: ID!, $msg_id_4: ID!, $msg_id_5: ID!){
  msg(msg_id: $msg_id){
		... fragmentMsg
  }
  msg2:msg(msg_id: $msg_id_2){
    ... fragmentMsg
  }
  msg3:msg(msg_id: $msg_id_3){
    ... fragmentMsg
  }
  msg4:msg(msg_id: $msg_id_4){
    ... fragmentMsg
  }
  msg5:msg(msg_id: $msg_id_5){
    ... fragmentMsg
  }
}
fragment fragmentMsg on BasicMsg {
  	msg_id msg_type timestamp room_id
    user{
    	... fragmentUser
    }
    msgContent{
      ... on MsgChatAndReView{
        msg_type msg_status content_text 
        target_user{
          ... fragmentUser
        }
      	target_msg{
          msg_id msg_type timestamp room_id
        }
        operator{
          ... fragmentUser
        }
      }
      ... on MsgDonateAndGift{
        msg_type trade_type trade_num content_text 
        target_user{
          ... fragmentUser
        }
      }
    }
}
fragment fragmentUser on BasicUser {
  user_id nick avatar user_type
}
```

```
{
  "msg_id": 20000,
  "msg_id_2": 20001,
  "msg_id_3": 20002,
  "msg_id_4": 30000,
  "msg_id_5": 30001
}
```

```
{
  "data": {
    "msg": {
      "msg_id": "20000",
      "msg_type": "chat_and_review",
      "timestamp": 1496991322,
      "room_id": 103,
      "user": {
        "user_id": "10042",
        "nick": "Nick10042",
        "avatar": "http://58jinrongyun.com/dist/dyy/view/jiaoyu/mobile/images/male.png",
        "user_type": "authorized"
      },
      "msgContent": {
        "msg_type": "chat_and_review",
        "msg_status": "review_add",
        "content_text": "BBB",
        "target_user": null,
        "target_msg": null,
        "operator": null
      }
    },
    "msg2": {
      "msg_id": "20001",
      "msg_type": "chat_and_review",
      "timestamp": 1496992021,
      "room_id": 102,
      "user": {
        "user_id": "10093",
        "nick": "Nick10093",
        "avatar": "http://58jinrongyun.com/dist/dyy/view/jiaoyu/mobile/images/male.png",
        "user_type": "authorized"
      },
      "msgContent": {
        "msg_type": "chat_and_review",
        "msg_status": "review_del",
        "content_text": "ABC",
        "target_user": null,
        "target_msg": {
          "msg_id": "20000",
          "msg_type": "chat_and_review",
          "timestamp": 1496991322,
          "room_id": 103
        },
        "operator": {
          "user_id": "1000",
          "nick": "Mgr1000",
          "avatar": "http://58jinrongyun.com/dist/dyy/view/jiaoyu/mobile/images/male.png",
          "user_type": "manager"
        }
      }
    },
    "msg3": {
      "msg_id": "20002",
      "msg_type": "chat_and_review",
      "timestamp": 1496991684,
      "room_id": 105,
      "user": {
        "user_id": "10091",
        "nick": "Nick10091",
        "avatar": "http://58jinrongyun.com/dist/dyy/view/jiaoyu/mobile/images/male.png",
        "user_type": "authorized"
      },
      "msgContent": {
        "msg_type": "chat_and_review",
        "msg_status": "review_add",
        "content_text": "DEF",
        "target_user": {
          "user_id": "10086",
          "nick": "Nick10086",
          "avatar": "http://58jinrongyun.com/dist/dyy/view/jiaoyu/mobile/images/male.png",
          "user_type": "authorized"
        },
        "target_msg": null,
        "operator": null
      }
    },
    "msg4": {
      "msg_id": "30000",
      "msg_type": "donate_and_gift",
      "timestamp": 1496992234,
      "room_id": 101,
      "user": {
        "user_id": "10093",
        "nick": "Nick10093",
        "avatar": "http://58jinrongyun.com/dist/dyy/view/jiaoyu/mobile/images/male.png",
        "user_type": "authorized"
      },
      "msgContent": {
        "msg_type": "donate_and_gift",
        "trade_type": "RMB",
        "trade_num": 48.34,
        "content_text": "RMB (x 48.34) ps:BBB",
        "target_user": {
          "user_id": "10080",
          "nick": "Nick10080",
          "avatar": "http://58jinrongyun.com/dist/dyy/view/jiaoyu/mobile/images/male.png",
          "user_type": "authorized"
        }
      }
    },
    "msg5": {
      "msg_id": "30001",
      "msg_type": "donate_and_gift",
      "timestamp": 1496991708,
      "room_id": 104,
      "user": {
        "user_id": "10070",
        "nick": "Nick10070",
        "avatar": "http://58jinrongyun.com/dist/dyy/view/jiaoyu/mobile/images/male.png",
        "user_type": "authorized"
      },
      "msgContent": {
        "msg_type": "donate_and_gift",
        "trade_type": "RMB",
        "trade_num": 178.23,
        "content_text": "RMB (x 178.23) ps:DEF",
        "target_user": {
          "user_id": "10015",
          "nick": "Nick10015",
          "avatar": "http://58jinrongyun.com/dist/dyy/view/jiaoyu/mobile/images/male.png",
          "user_type": "authorized"
        }
      }
    }
  }
}
```