Ctries = linspace(0.005, 0.1, 1000);
runTimes = zeros(size(Ctries));

IdrawTries = linspace(0.035, 0.2, 100);
runTimes = zeros(size(IdrawTries));

for i = 1:length(IdrawTries)
  # C = Ctries(i);
  Idraw = IdrawTries(i);


  t = linspace(0, 11, 1000);

  step = t(2);

  pulseLength = 2;

  # Supply voltage minus diod drop
  V0 = 7.8;

  # In order to follow transformer spec we must limit current to 1A
  R = 10;

  C = 0.094;
  C = 0.047;
  C = 0.047/2;

  # Worst case current consumption is 200mA, best case is 35mA
  # Idraw = 0.2;
  # Idraw = 0.1;

  # Arduino needs at least 5V
  Vreg = 5;

  Vcap = zeros(size(t));

  currentVcap = 0;
  currentTimeIndex = 1;

  while currentVcap < Vreg && t(currentTimeIndex) < pulseLength
    currentVcap = V0*(1 - exp(-t(currentTimeIndex)/R/C));
    Vcap(currentTimeIndex) = currentVcap;
    currentTimeIndex += 1;
  endwhile

  arduinoStartAtTime = t(currentTimeIndex);

  while t(currentTimeIndex) < pulseLength
    dischargeTime = t(currentTimeIndex) - arduinoStartAtTime;
    currentVcap = V0*(1 - exp(-t(currentTimeIndex)/R/C));
    currentVcap -= step*Idraw/currentVcap/C;
    Vcap(currentTimeIndex) = currentVcap;
    currentTimeIndex += 1;
  endwhile

  while currentVcap > Vreg && t(currentTimeIndex) < 11
    currentVcap -= step*Idraw/C/currentVcap;
    Vcap(currentTimeIndex) = currentVcap;
    currentTimeIndex += 1;
  endwhile

  arduinoRunTime = t(currentTimeIndex) - arduinoStartAtTime;

  runTimes(i) = arduinoRunTime;

endfor

#hold on;
plot(IdrawTries, runTimes);
# plot(Ctries, runTimes);

#hold off;
#plot(t, Vcap);
#hold on;
#plot([arduinoRunTime arduinoRunTime], [0, V0]);