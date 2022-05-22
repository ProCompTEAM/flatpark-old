namespace DataCenter.Infrastructure.Subcomponents.Interfaces
{
    public interface ILogger
    {
        void Info(string message, string prefix, ConsoleColor color);
    }
}