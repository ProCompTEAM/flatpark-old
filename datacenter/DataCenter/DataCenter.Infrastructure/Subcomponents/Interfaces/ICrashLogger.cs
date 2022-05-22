namespace DataCenter.Infrastructure.Subcomponents.Interfaces
{
    public interface ICrashLogger
    {
        void Crash(string description, string[] traces);
    }
}