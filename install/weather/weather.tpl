<!-- BEGIN weather -->
<div style="margin: 4px; border: 1px solid; width: 320px;font-size: 10px;">
  <div style="padding: 2px 4px; color: #93203F; font-weight: bold; font-size: 14px; background-color: #F0F0F0;">Conditions actuelles&nbsp;<span style="padding:0; margin: 0; font-size: 7px; color: black;">({weather.UPDATE})</span></div>
  <div style="padding: 2px 4px; display: block; overflow: auto;">
    <div style="display: block; float: right; overflow: hidden; width: 93px;"><img src="{weather.LARGE_PICT}" alt="{weather.TENDANCE}" style="border: none;"></div>
	  <div style="float: left; display: block; overflow: hidden; width:205px;">
	    <p style="padding: 0; margin: 0;"><label style="font-weight: bold;">{weather.LIB_CITY}&nbsp;:&nbsp;</label>{weather.CITY}</p>
      <p style="padding: 0; margin: 0; font-weight: bold; color: #93203F; font-size: 12px;">{weather.TENDANCE}</p>
      <div>
        <div style="float: right; overflow: hidden; text-align: center;">
          <p style="padding: 0; margin: 0; font-weight: bold; color: #93203F; font-size: 20px;">{weather.TEMP}</p>
          <p style="padding: 0; margin: 0; font-weight: bold; color: #93203F; font-size: 9px;">({weather.FEEL})</p>
        </div>
        <div style="display: block; overflow: hidden;">
		      <p style="padding: 0; margin: 0;"><label style="font-weight: bold;">{weather.LIB_WIND}&nbsp;:&nbsp;</label>{weather.WIND}&nbsp;{weather.WIND_DIRECTION}</p>
		      <p style="padding: 0; margin: 0;"><label style="font-weight: bold;">{weather.LIB_HUMIDITY}&nbsp;:&nbsp;</label>{weather.HUMIDITY}</p>
		      <!-- BEGIN sun -->
		      <p style="padding: 0; margin: 0;"><label style="font-weight: bold;">{weather.LIB_DAWN}&nbsp;:&nbsp;</label>{weather.sun.DAWN}</p>
		      <p style="padding: 0; margin: 0;"><label style="font-weight: bold;">{weather.LIB_DUSK}&nbsp;:&nbsp;</label>{weather.sun.DUSK}</p>
		      <!-- END sun -->
			  </div>
		  </div>
		</div>
	</div>
  <!-- BEGIN this_PM -->
  <p style="padding: 2px 4px; margin: 0; font-weight: bold; font-size: 12px; background-color: #F0F0F0;">Cette après-midi</p>
  <div style="padding: 2px 4px; display: block; overflow: auto;">
    <div style="display: block; float: right; overflow: hidden; width: 93px; text-align: center; color: #93203F; font-weight: bold;"><img src="{weather.this_PM.SMALL_PICT}" alt="" style="border: none;" /></div>
    <div style="display: block; overflow: hidden;">
      <p style="padding: 2px 4px; margin: 0;"><label style="font-weight: bold;">{weather.LIB_TEMP}&nbsp;{weather.LIB_MINI}/{weather.LIB_MAXI}&nbsp;:&nbsp;</label>{weather.this_PM.TEMP_DOWN}/{weather.this_PM.TEMP_UP}</p>
		  <p style="padding: 2px 4px; margin: 0;"><label style="font-weight: bold;">{weather.LIB_RAIN_PROBAB}&nbsp;:&nbsp;</label>{weather.this_PM.RAIN_PROBAB}</p>
		</div>
  </div>
  <!-- END this_PM -->
  <!-- BEGIN this_night -->
  <p style="padding: 2px 4px; margin: 0; font-weight: bold; font-size: 12px; background-color: #F0F0F0;">Cette nuit</p>
  <div style="padding: 2px 4px; display: block; overflow: auto;">
	  <div style="display: block; float: right; overflow: hidden; width: 93px; text-align: center; color: #93203F; font-weight: bold;"><img src="{weather.this_night.SMALL_PICT}" alt="" style="border: none;"></div>
	  <div style="display: block; overflow: hidden;">
		  <p style="padding: 0; margin: 0;"><label style="font-weight: bold;">{weather.LIB_TEMP}&nbsp;:&nbsp;</label>{weather.this_night.TEMP}</p>
	  </div>
	</div>
  <!-- END this_night -->
  <p style="padding: 0 4px; margin: 0; background-color: #F0F0F0; text-align: right; font-size: 9px;"><a href="http://www.123meteo.com" style="color: black;">{weather.LIB_WEATHER_COM} weather.com</a></p>
  <!-- BEGIN preview -->
  <p style="padding: 2px; margin: 0; border-top: 1px solid; font-weight: bold; background-color: #F0F0F0; text-align: center;">{weather.preview.NAME_DAY}&nbsp;{weather.preview.DATE}</p>
  <div style="padding: 2px 4px; display: block; overflow: auto;">
    <!-- BEGIN days -->
    <div style="display: block; float: right; overflow: hidden; width: 93px; text-align: center; color: #93203F; font-weight: bold;"><img src="{weather.preview.days.MEDIUM_PICT}" alt="" style="border: none;" /></div>
    <div style="display: block; overflow: hidden;">
      <p style="padding: 0; margin: 0;"><label style="font-weight: bold;">{weather.LIB_TEMP}&nbsp;{weather.LIB_MINI}/{weather.LIB_MAXI}&nbsp;:&nbsp;</label>{weather.preview.TEMP_DOWN}/{weather.preview.TEMP_UP}</p>
      <p style="padding: 0; margin: 0;"><label style="font-weight: bold;">Matin&nbsp;:&nbsp;</label>{weather.preview.days.TENDANCE_AM}</p>
      <p style="padding: 0; margin: 0;"><label style="font-weight: bold;">A-midi&nbsp;:&nbsp;</label>{weather.preview.days.TENDANCE_PM}</p>
      <p style="padding: 0; margin: 0;"><label style="font-weight: bold;">{weather.LIB_RAIN_PROBAB}&nbsp;:&nbsp;</label>{weather.preview.days.RAIN_PROBAB}</p>
    </div>
    <!-- END days -->
  </div>
  <!-- END preview -->
</div>
<!-- END weather -->
