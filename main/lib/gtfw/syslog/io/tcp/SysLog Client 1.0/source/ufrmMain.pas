unit ufrmMain;

interface

uses
  SysLogClient,
  Windows, Messages, SysUtils, Variants, Classes, Graphics, Controls, Forms,
  Dialogs, StdCtrls, IdBaseComponent, IdComponent, IdCustomTCPServer,
  ComCtrls, XPMan, ExtCtrls, Menus, AppEvnts;

type
  TLogWindowOptions = class
  strict private
    FHideOnMinimize: Boolean;
  public
    constructor Create;
    property HideOnMinimize: Boolean read FHideOnMinimize write FHideOnMinimize;
  end;


  TfrmMain = class(TForm)
    edtMain: TRichEdit;
    pnlMain: TFlowPanel;
    XPManifest1: TXPManifest;
    btnClear: TButton;
    btnHide: TButton;
    btnSave: TButton;
    dlgsaveLog: TSaveDialog;
    TrayIcon1: TTrayIcon;
    mainmnu: TMainMenu;
    System1: TMenuItem;
    Exit1: TMenuItem;
    Log1: TMenuItem;
    SaveLogAs1: TMenuItem;
    Clear1: TMenuItem;
    N1: TMenuItem;
    ools1: TMenuItem;
    Options1: TMenuItem;
    HidetoTray1: TMenuItem;
    N2: TMenuItem;
    pmnuMain: TPopupMenu;
    Show1: TMenuItem;
    ApplicationEvents: TApplicationEvents;
    N3: TMenuItem;
    Close1: TMenuItem;
    StatusBar1: TStatusBar;
    N4: TMenuItem;
    AlwaysOnTop1: TMenuItem;
    procedure AlwaysOnTop1Click(Sender: TObject);
    procedure Options1Click(Sender: TObject);
    procedure Close1Click(Sender: TObject);
    procedure ApplicationEventsMinimize(Sender: TObject);
    procedure FormDestroy(Sender: TObject);
    procedure FormCreate(Sender: TObject);
    procedure Show1Click(Sender: TObject);
    procedure Clear1Click(Sender: TObject);
    procedure SaveLogAs1Click(Sender: TObject);
    procedure Exit1Click(Sender: TObject);
    procedure HidetoTray1Click(Sender: TObject);
    procedure TrayIcon1DblClick(Sender: TObject);
    procedure btnSaveClick(Sender: TObject);
    procedure btnHideClick(Sender: TObject);
    procedure btnClearClick(Sender: TObject);
  private
    FSysLogClient: TSyslogClient;
    FLogWindowOption: TLogWindowOptions;

    procedure OnActivate(Sender: TSyslogClient);
    procedure OnStop(Sender: TSyslogClient);
    procedure OnLog(Sender: TSyslogClient; Args: TGetLogEventArgs);

    procedure SaveLog;
    procedure ClearLog;
    procedure ShowOptions;
    procedure UpdateStatus;
    procedure ShowTrayHint;
  public
    { Public declarations }
  end;

var
  frmMain: TfrmMain;

implementation

uses ufrmOptions;

{$R *.dfm}


procedure TfrmMain.AlwaysOnTop1Click(Sender: TObject);
begin
  if Self.FormStyle = fsStayOnTop then
    Self.FormStyle := fsNormal
  else
    Self.FormStyle := fsStayOnTop;

  AlwaysOnTop1.Checked := Self.FormStyle = fsStayOnTop;
end;

procedure TfrmMain.ApplicationEventsMinimize(Sender: TObject);
begin
  {Aneh: Kenapa kalau diminimize untuk kedua kali, event ini gak aktif?}
//  if FLogWindowOption.HideOnMinimize then
//    Hide;
end;

procedure TfrmMain.btnClearClick(Sender: TObject);
begin
  ClearLog;
end;

procedure TfrmMain.btnHideClick(Sender: TObject);
begin
  Hide;
end;

procedure TfrmMain.btnSaveClick(Sender: TObject);
begin
  SaveLog;
end;

procedure TfrmMain.Exit1Click(Sender: TObject);
begin
  Application.Terminate;
end;

procedure TfrmMain.FormCreate(Sender: TObject);
begin
  FLogWindowOption := TLogWindowOptions.Create;

  FSysLogClient := TSyslogClient.Create;
  FSysLogClient.OnLog := OnLog;
  FSysLogClient.OnActivate := OnActivate;
  FSysLogClient.OnStop := OnStop;
  
  ShowOptions;
  UpdateStatus;
end;

procedure TfrmMain.FormDestroy(Sender: TObject);
begin
  FLogWindowOption.Free;
  FSysLogClient.Free;
end;

procedure TfrmMain.HidetoTray1Click(Sender: TObject);
begin
  Hide;
end;

procedure TfrmMain.OnActivate(Sender: TSyslogClient);
begin
  ShowTrayHint;
  UpdateStatus;
end;

procedure TfrmMain.OnLog(Sender: TSyslogClient; Args: TGetLogEventArgs);
begin
  edtMain.Lines.Add(Args.Text);
end;

procedure TfrmMain.OnStop(Sender: TSyslogClient);
begin
  UpdateStatus;
end;

procedure TfrmMain.Options1Click(Sender: TObject);
begin
  ShowOptions;
end;

procedure TfrmMain.SaveLog;
begin
  if dlgsaveLog.Execute then
    edtMain.Lines.SaveToFile(dlgsaveLog.FileName);
end;

procedure TfrmMain.Clear1Click(Sender: TObject);
begin
  ClearLog;
end;

procedure TfrmMain.ClearLog;
begin
  edtMain.Clear;
end;

procedure TfrmMain.ShowOptions;
begin
  frmOptions := TfrmOptions.Create(Self);
  frmOptions.SysLogClient := Self.FSysLogClient;
  frmOptions.ShowModal;
end;

procedure TfrmMain.UpdateStatus;
begin
  if FSysLogClient.Active then
    StatusBar1.Panels[0].Text := Format('Status: Active   Port: %d', [FSysLogClient.Options.Port])
  else
    StatusBar1.Panels[0].Text := 'Status: not active';
end;

procedure TfrmMain.ShowTrayHint;
begin
  TrayIcon1.BalloonTitle := 'SysLog Client';
  TrayIcon1.BalloonHint := Format('SysLogClient activated.' + ''#13''#10'' + 'Listening at port: %d', [FSysLogClient.Options.Port]);
  TrayIcon1.ShowBalloonHint;
end;

procedure TfrmMain.Close1Click(Sender: TObject);
begin
  Application.Terminate;
end;

procedure TfrmMain.SaveLogAs1Click(Sender: TObject);
begin
  SaveLog;
end;

procedure TfrmMain.Show1Click(Sender: TObject);
begin
  Show;
end;

procedure TfrmMain.TrayIcon1DblClick(Sender: TObject);
begin
  Show;
  Application.BringToFront;
end;

{ TLogWindowOptions }

constructor TLogWindowOptions.Create;
begin
  FHideOnMinimize := True;
end;

{ TSyslogOptions }

end.
