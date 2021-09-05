<?php
namespace Events;

class Cmd
{

	 //cmd number
    const  DATA_START='@B#@';
    const DATA_END='@E#@';
    //up cmd
    const  LK_NUM='003';
    const  INIT_NUM='001';
    const  SYNC_NUM='002';
    const  WEATHER_NUM='004';
    const  UD_NUM='005';
    const  AL_NUM='006';
    const  BAT_NUM='007';

    //down cmd
    const  SERVER_ACK_NUM='100';
    const  IP_NUM='101';
    const  FAMILY_PHONE_NUM='102';
    const  CONFIG_NUM='103';
    const  CONTROL_NUM='104'; 
	
}