unit SysLogClient;

interface

uses
  IdTCPServer, IdContext;

type
  TSyslogOptions = class
  strict private
    FPort: Integer;
  public
    constructor Create;
    property Port: Integer read FPort write FPort;
  end;

  TSyslogClient = class;

  TSysLogClientNotifyEvent = procedure (Sender: TSyslogClient) of object;

  TGetLogEventArgs = class
  strict private
    FText: string;
  public
    property Text: string read FText write FText;
  end;

  TGetLogNotifyEvent = procedure (Sender: TSyslogClient; Args: TGetLogEventArgs) of object;

  TSyslogClient = class
  strict private
    FTcpServer: TIdTcpServer;
    FOptions: TSyslogOptions;

    FOnActivate: TSysLogClientNotifyEvent;
    FOnStop: TSysLogClientNotifyEvent;
    FOnLog: TGetLogNotifyEvent;
  private
    function GetActive: Boolean;
    procedure SetActive(const Value: Boolean);
    procedure TCPServerExecute(AContext: TIdContext);
  protected
    procedure DoLog(Args: TGetLogEventArgs); dynamic;
    procedure DoActivate; dynamic;
    procedure DoStop; dynamic;
  public
    constructor Create;
    destructor Destroy; override;

    procedure Activate;
    procedure Stop;

    property Active: Boolean read GetActive write SetActive;
    property Options: TSyslogOptions read FOptions;
    property OnLog: TGetLogNotifyEvent read FOnLog write FOnLog;
    // Fires after syslog client is activated
    property OnActivate: TSysLogClientNotifyEvent read FOnActivate write FOnActivate;
    property OnStop: TSysLogClientNotifyEvent read FOnStop write FOnStop;
  end;

implementation

{ TSyslogOptions }

constructor TSyslogOptions.Create;
begin
  FPort := 9000;
end;

{ TSyslogClient }

procedure TSyslogClient.Activate;
begin
  if not FTcpServer.Active then
  begin
    FTcpServer.DefaultPort := FOptions.Port;
    FTcpServer.OnExecute := TCPServerExecute;
    FTcpServer.Active := True;

    DoActivate;
  end;
end;

constructor TSyslogClient.Create;
begin
  FTcpServer := TIdTCPServer.Create(nil);
  FOptions := TSyslogOptions.Create;
end;

destructor TSyslogClient.Destroy;
begin
  FTcpServer.Active := False;
  FTcpServer.Free;

  FOptions.Free;
  inherited;
end;

procedure TSyslogClient.DoActivate;
begin
  if Assigned(FOnActivate) then
    FOnActivate(Self);
end;

procedure TSyslogClient.DoLog(Args: TGetLogEventArgs);
begin
  if Assigned(FOnLog) then
    FOnLog(Self, Args);
end;

procedure TSyslogClient.DoStop;
begin
  if Assigned(FOnStop) then
    FOnStop(Self);
end;

function TSyslogClient.GetActive: Boolean;
begin
  Result := FTcpServer.Active;
end;

procedure TSyslogClient.SetActive(const Value: Boolean);
begin
  Activate;
end;

procedure TSyslogClient.Stop;
begin
  if FTcpServer.Active then
  begin
    FTcpServer.Active := False;
    DoStop;
  end;
end;

procedure TSyslogClient.TCPServerExecute(AContext: TIdContext);
var
  lLogString: string;
  lArgs: TGetLogEventArgs;
begin
  lLogString := AContext.Connection.IOHandler.ReadLn;

  lArgs := TGetLogEventArgs.Create;
  try
    lArgs.Text := lLogString;
    DoLog(lArgs);
  finally
    lArgs.Free;
  end;
end;

end.
