using System;

namespace DataCenter.Infrastructure.Generic.Interfaces
{
    public interface ILogger
    {
        void Info(string message, string prefix, ConsoleColor color);
    }
}