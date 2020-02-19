program SysLog;

uses
  Forms,
  ufrmMain in 'ufrmMain.pas' {frmMain},
  SysLogClient in 'SysLogClient.pas',
  ufrmOptions in 'ufrmOptions.pas' {frmOptions};

{$R *.res}

begin
  Application.Initialize;
  Application.Title := 'SysLog Client';
  Application.CreateForm(TfrmMain, frmMain);
  Application.Run;
end.
